<?php

namespace App\Services;

use App\Dto\PaginatedResultDto;
use App\Dto\ProductFiltersDto;
use App\Dto\ProductListQueryDto;
use App\Dto\ProductDto;
use App\Dto\ProductSearchQueryDto;
use App\Dto\ProductSearchResultDto;
use App\Dto\UploadImageDto;
use App\Infrastructure\Interfaces\ElasticsearchClientInterface;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

final readonly class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FileService $fileService,
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

    public function createProduct(ProductDto $dto): ProductDto
    {
        return $this->transactionManager->transaction(function () use ($dto) {
            $productDto = $this->productRepository->create($dto);

            $this->handleImages($productDto->id, $dto->images);

            return $this->productRepository->findById($productDto->id);
        });
    }

    public function updateProduct(int $id, ProductDto $dto): ProductDto
    {
        return $this->transactionManager->transaction(function () use ($id, $dto) {
            $product = $this->productRepository->findById($id);

            if (!$product) {
                throw new ModelNotFoundException("Product with ID {$id} not found.");
            }

            $this->productRepository->update($id, $dto);

            if (!empty($dto->images)) {
                $this->productRepository->deleteImages($id);
                $this->handleImages($id, $dto->images);
            }

            return $this->productRepository->findById($id);
        });
    }

    public function deleteProduct(int $id): void
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new ModelNotFoundException("Product with ID {$id} not found.");
        }

        $this->productRepository->deleteImages($id);
        $this->productRepository->delete($id);
    }

    public function search(ProductSearchQueryDto $queryDto): ProductSearchResultDto
    {
        $query = $queryDto->query;
        $categoryIds = $queryDto->categoryIds;
        $minPrice = $queryDto->minPrice;
        $maxPrice = $queryDto->maxPrice;
        $sort = $queryDto->sort;
        $perPage = $queryDto->perPage;
        $page = $queryDto->page;

        $params = [
            'index' => 'products_index',
            'body'  => [
                'from'  => ($page - 1) * $perPage,
                'size'  => $perPage,
                'query' => [
                    'bool' => [
                        'must'   => [],
                        'filter' => [
                            ['term' => ['is_active' => true]]
                        ]
                    ]
                ],
                'aggs'  => [
                    'categories' => [
                        'terms' => ['field' => 'category_id', 'size' => 50]
                    ],
                    'min_price'  => ['min' => ['field' => 'price']],
                    'max_price'  => ['max' => ['field' => 'price']]
                ]
            ]
        ];

        if (!empty($query)) {
            $params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query'     => $query,
                    'fields'    => ['name^3', 'description', 'category_name'],
                    'fuzziness' => 'AUTO'
                ]
            ];
        }

        if (!empty($categoryIds)) {
            $params['body']['query']['bool']['filter'][] = [
                'terms' => ['category_id' => $categoryIds]
            ];
        }

        if ($minPrice !== null || $maxPrice !== null) {
            $range = ['price' => []];
            if ($minPrice !== null) $range['price']['gte'] = $minPrice;
            if ($maxPrice !== null) $range['price']['lte'] = $maxPrice;
            $params['body']['query']['bool']['filter'][] = ['range' => $range];
        }

        switch ($sort) {
            case 'price_asc':
                $params['body']['sort'] = [['price' => 'asc']];
                break;
            case 'price_desc':
                $params['body']['sort'] = [['price' => 'desc']];
                break;
            case 'name_asc':
                $params['body']['sort'] = [['name.keyword' => 'asc']];
                break;
            default:
                $params['body']['sort'] = [['created_at' => 'desc']];
        }

        try {
            $results = $this->elasticsearch->search($params);
            
            $hits = $results['hits'];
            $hitItems = $hits['hits'];
            $ids = array_map(fn ($hit) => (int) $hit['_id'], $hitItems);
            
            $products = [];
            if (!empty($ids)) {
                $fetchedProducts = [];
                foreach ($ids as $id) {
                    $p = $this->productRepository->findById($id);
                    if ($p) $fetchedProducts[] = $p;
                }
                $products = $fetchedProducts;
            }

            $aggregations = $results['aggregations'] ?? [];
            $total = $hits['total']['value'];

            return new ProductSearchResultDto(
                data: $products,
                meta: [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'last_page'    => ceil($total / $perPage),
                ],
                filters: [
                    'categories' => collect($aggregations['categories']['buckets'] ?? [])
                        ->map(fn ($b) => [
                            'id'    => (int) $b['key'],
                            'count' => (int) $b['doc_count']
                        ])->toArray(),
                    'min_price'  => $aggregations['min_price']['value'],
                    'max_price'  => $aggregations['max_price']['value'],
                ]
            );

        } catch (Exception $e) {
            Log::warning("Elasticsearch search failed: " . $e->getMessage());
            return $this->fallbackSearch($queryDto);
        }
    }

    private function fallbackSearch(ProductSearchQueryDto $queryDto): ProductSearchResultDto
    {
        $filters = new ProductFiltersDto(
            categoryId: $queryDto->categoryIds !== [] ? $queryDto->categoryIds[0] : null,
            isActive: true,
        );

        $paginator = $this->productRepository->getAll($filters, $queryDto->perPage);

        return new ProductSearchResultDto(
            data: $paginator->items,
            meta: [
                'total' => $paginator->total,
                'per_page' => $paginator->perPage,
                'current_page' => $paginator->currentPage,
                'last_page' => $paginator->lastPage,
            ],
            filters: [
                'categories' => [],
                'min_price'  => 0,
                'max_price'  => 0,
            ]
        );
    }

    /** @param array<int, UploadedFile> $images */
    private function handleImages(int $productId, array $images): void
    {
        /** @var string $disk */
        $disk = config('filesystems.media_disk', 's3');

        foreach ($images as $file) {
            $dto = new UploadImageDto($file, 'products', $disk);
            $pathOrUrl = $this->fileService->upload($dto);
            $this->productRepository->saveImage($productId, $pathOrUrl);
        }
    }
}
