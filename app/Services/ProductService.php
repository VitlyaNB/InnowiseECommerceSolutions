<?php

namespace App\Services;

use App\Dto\PaginatedResultDto;
use App\Dto\ProductDto;
use App\Dto\ProductFiltersDto;
use App\Dto\ProductListQueryDto;
use App\Dto\ProductSearchQueryDto;
use App\Dto\ProductSearchResultDto;
use App\Dto\UploadImageDto;
use App\Infrastructure\Interfaces\ElasticsearchClientInterface;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\Interfaces\FileServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

final readonly class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FileServiceInterface $fileService,
        private TransactionManagerInterface $transactionManager,
        private ElasticsearchClientInterface $elasticsearch
    ) {}

    public function getAllProducts(ProductListQueryDto $queryDto): PaginatedResultDto
    {
        return $this->productRepository->getAll($queryDto->filters, $queryDto->perPage);
    }

    /** @return array<int, ProductDto> */
    public function getProductsByCategory(int $categoryId): array
    {
        return $this->productRepository->getByCategory($categoryId);
    }

    public function getProductById(int $id): ?ProductDto
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(ProductDto $dto): ?ProductDto
    {
        /** @var ProductDto|null $result */
        $result = $this->transactionManager->transaction(function () use ($dto): ProductDto|null {
            $productDto = $this->productRepository->create($dto);

            if ($productDto->id === null) {
                return null;
            }

            $this->handleImages($productDto->id, $dto->images);

            return $this->productRepository->findById($productDto->id);
        });

        return $result;
    }

    public function updateProduct(int $id, ProductDto $dto): ?ProductDto
    {
        /** @var ProductDto|null $result */
        $result = $this->transactionManager->transaction(function () use ($id, $dto): ProductDto|null {
            $product = $this->productRepository->findById($id);

            if (! $product) {
                throw new ModelNotFoundException("Product with ID {$id} not found.");
            }

            $this->productRepository->update($id, $dto);

            if (! empty($dto->images)) {
                $this->productRepository->deleteImages($id);
                $this->handleImages($id, $dto->images);
            }

            return $this->productRepository->findById($id);
        });

        return $result;
    }

    public function deleteProduct(int $id): void
    {
        $product = $this->productRepository->findById($id);

        if (! $product) {
            throw new ModelNotFoundException("Product with ID {$id} not found.");
        }

        $this->productRepository->deleteImages($id);
        $this->productRepository->deleteOrderItemsByProductId($id);
        $this->productRepository->delete($id);
    }

    public function search(ProductSearchQueryDto $queryDto): ProductSearchResultDto
    {
        try {
            $params = $this->buildElasticsearchQuery($queryDto);
            $results = $this->executeSearch($params);
            $products = $this->mapHitsToProducts($results);
            $filters = $this->extractFilters($results, $queryDto->perPage, $queryDto->page);

            return new ProductSearchResultDto(
                data: $products,
                meta: $filters['meta'],
                filters: $filters['filters']
            );
        } catch (Exception $e) {
            Log::warning('Elasticsearch search failed: '.$e->getMessage());

            return $this->fallbackSearch($queryDto);
        }
    }

    private function buildElasticsearchQuery(ProductSearchQueryDto $queryDto): array
    {
        $params = [
            'index' => 'products_index',
            'body' => [
                'from' => ($queryDto->page - 1) * $queryDto->perPage,
                'size' => $queryDto->perPage,
                'query' => [
                    'bool' => [
                        'must' => [],
                        'filter' => [
                            ['term' => ['is_active' => true]],
                        ],
                    ],
                ],
                'aggs' => [
                    'categories' => [
                        'terms' => ['field' => 'category_id', 'size' => 50],
                    ],
                    'min_price' => ['min' => ['field' => 'price']],
                    'max_price' => ['max' => ['field' => 'price']],
                ],
            ],
        ];

        if (! empty($queryDto->query)) {
            $params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query' => $queryDto->query,
                    'fields' => ['name^3', 'description', 'category_name'],
                    'fuzziness' => 'AUTO',
                ],
            ];
        }

        if (! empty($queryDto->categoryIds)) {
            $params['body']['query']['bool']['filter'][] = [
                'terms' => ['category_id' => $queryDto->categoryIds],
            ];
        }

        if ($queryDto->minPrice !== null || $queryDto->maxPrice !== null) {
            $range = ['price' => []];
            if ($queryDto->minPrice !== null) {
                $range['price']['gte'] = $queryDto->minPrice;
            }
            if ($queryDto->maxPrice !== null) {
                $range['price']['lte'] = $queryDto->maxPrice;
            }
            $params['body']['query']['bool']['filter'][] = ['range' => $range];
        }

        $params['body']['sort'] = match ($queryDto->sort) {
            'price_asc' => [['price' => 'asc']],
            'price_desc' => [['price' => 'desc']],
            'name_asc' => [['name.keyword' => 'asc']],
            default => [['created_at' => 'desc']],
        };

        return $params;
    }

    private function executeSearch(array $params): array
    {
        /** @var array<string, mixed> $results */
        return $this->elasticsearch->search($params);
    }

    private function mapHitsToProducts(array $results): array
    {
        /** @var array<string, mixed> $hits */
        $hits = $results['hits'] ?? [];
        /** @var array<int, array<string, mixed>> $hitItems */
        $hitItems = $hits['hits'] ?? [];
        /** @var array<int, int> $ids */
        $ids = [];
        foreach ($hitItems as $hit) {
            $id = $hit['_id'] ?? null;
            $ids[] = is_numeric($id) ? (int) $id : 0;
        }

        /** @var array<int, ProductDto> $products */
        $products = [];
        if (! empty($ids)) {
            foreach ($ids as $id) {
                $p = $this->productRepository->findById($id);
                if ($p) {
                    $products[] = $p;
                }
            }
        }

        return $products;
    }

    private function extractFilters(array $results, int $perPage, int $page): array
    {
        /** @var array<string, mixed> $hits */
        $hits = $results['hits'] ?? [];
        /** @var array<string, mixed> $aggregations */
        $aggregations = $results['aggregations'] ?? [];

        /** @var int $total */
        $total = 0;
        $hitsTotal = $hits['total'] ?? null;
        if (is_array($hitsTotal) && isset($hitsTotal['value']) && is_numeric($hitsTotal['value'])) {
            $total = (int) $hitsTotal['value'];
        }

        /** @var array<int, array<string, int>> $categoryBuckets */
        $categoryBuckets = [];
        $categoriesAgg = $aggregations['categories'] ?? null;
        if (is_array($categoriesAgg) && isset($categoriesAgg['buckets']) && is_array($categoriesAgg['buckets'])) {
            foreach ($categoriesAgg['buckets'] as $bucket) {
                if (is_array($bucket)) {
                    $bucketKey = $bucket['key'] ?? null;
                    $bucketDocCount = $bucket['doc_count'] ?? null;
                    $categoryBuckets[] = [
                        'id' => is_numeric($bucketKey) ? (int) $bucketKey : 0,
                        'count' => is_numeric($bucketDocCount) ? (int) $bucketDocCount : 0,
                    ];
                }
            }
        }

        /** @var float $minPrice */
        $minPrice = 0.0;
        $minPriceAgg = $aggregations['min_price'] ?? null;
        if (is_array($minPriceAgg) && isset($minPriceAgg['value']) && is_numeric($minPriceAgg['value'])) {
            $minPrice = (float) $minPriceAgg['value'];
        }

        /** @var float $maxPrice */
        $maxPrice = 0.0;
        $maxPriceAgg = $aggregations['max_price'] ?? null;
        if (is_array($maxPriceAgg) && isset($maxPriceAgg['value']) && is_numeric($maxPriceAgg['value'])) {
            $maxPrice = (float) $maxPriceAgg['value'];
        }

        return [
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $total > 0 ? (int) ceil($total / $perPage) : 1,
            ],
            'filters' => [
                'categories' => $categoryBuckets,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ],
        ];
    }

    private function fallbackSearch(ProductSearchQueryDto $queryDto): ProductSearchResultDto
    {
        $filters = new ProductFiltersDto(
            categoryId: $queryDto->categoryIds !== [] ? $queryDto->categoryIds[0] : null,
            isActive: true,
        );

        $paginator = $this->productRepository->getAll($filters, $queryDto->perPage);

        /** @var array<int, ProductDto> $productData */
        $productData = $paginator->items;

        return new ProductSearchResultDto(
            data: $productData,
            meta: [
                'total' => $paginator->total,
                'per_page' => $paginator->perPage,
                'current_page' => $paginator->currentPage,
                'last_page' => $paginator->lastPage,
            ],
            filters: [
                'categories' => [],
                'min_price' => 0,
                'max_price' => 0,
            ]
        );
    }

    /**
     * @param  array<int, string|UploadedFile>  $images
     */
    private function handleImages(int $productId, array $images): void
    {
        /** @var string $disk */
        $disk = config('filesystems.media_disk', 's3');

        foreach ($images as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $dto = new UploadImageDto($file, 'products', $disk);
            $pathOrUrl = $this->fileService->upload($dto);
            $this->productRepository->saveImage($productId, $pathOrUrl);
        }
    }
}
