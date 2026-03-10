<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Interfaces\ProductViewRepositoryInterface;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class RecommendationService
{
    public function __construct(
        private ProductViewRepositoryInterface $productViewRepository
    ) {}

    public function recordView(?int $userId, string $sessionId, int $productId): void
    {
        $this->productViewRepository->recordView($userId, $sessionId, $productId);
    }

    /** @return array{also_bought: Collection<int, Product>, similar: Collection<int, Product>, recently_viewed: Collection<int, Product>} */
    public function getProductRecommendations(int $productId, ?int $userId, string $sessionId, int $limit = 8): array
    {
        /** @var Product $product */
        $product = Product::query()
            ->with('category')
            ->findOrFail($productId);

        $alsoBoughtIds = $this->getAlsoBoughtIds($productId, $limit);
        $similarIds = $this->getSimilarIds($product, $limit);
        $recentlyViewedIds = $this->productViewRepository->getRecentlyViewedProductIds($userId, $sessionId, $limit);

        return [
            'also_bought' => $this->getProductsByIds($alsoBoughtIds),
            'similar' => $this->getProductsByIds($similarIds),
            'recently_viewed' => $this->getProductsByIds($recentlyViewedIds, true),
        ];
    }

    /** @return Collection<int, Product> */
    public function getHomeRecommendations(?int $userId, string $sessionId, int $limit = 12): Collection
    {
        $recentIds = $this->productViewRepository->getRecentlyViewedProductIds($userId, $sessionId, $limit);

        if (!empty($recentIds)) {
            /** @var Product|null $seedProduct */
            $seedProduct = Product::query()->find($recentIds[0]);
            $similarIds = $seedProduct ? $this->getSimilarIds($seedProduct, $limit) : [];

            $merged = $this->uniqueIds(array_merge($recentIds, $similarIds));
            $products = $this->getProductsByIds($merged, true);

            if ($products->count() >= $limit) {
                return $products->take($limit);
            }

            return $this->fillWithRandom($products, $limit);
        }

        return $this->getRandomProducts($limit);
    }

    /** @return array<int, int> */
    private function getAlsoBoughtIds(int $productId, int $limit): array
    {
        /** @var array<int, int> $ids */
        $ids = DB::table('order_items as oi')
            ->join('order_items as oi2', 'oi.order_id', '=', 'oi2.order_id')
            ->where('oi.product_id', $productId)
            ->where('oi2.product_id', '!=', $productId)
            ->select('oi2.product_id', DB::raw('COUNT(*) as freq'))
            ->groupBy('oi2.product_id')
            ->orderByDesc('freq')
            ->limit($limit)
            ->pluck('oi2.product_id')
            ->all();
        return $ids;
    }

    /** @return array<int, int> */
    private function getSimilarIds(Product $product, int $limit): array
    {
        try {
            $client = app(Client::class);

            /** @var \Elastic\Elasticsearch\Response\Elasticsearch $response */
            $response = $client->search([
                'index' => $product->searchableAs(),
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'multi_match' => [
                                        'query' => $product->name,
                                        'fields' => ['name^5', 'description'],
                                        'fuzziness' => 'AUTO',
                                    ],
                                ],
                            ],
                            'filter' => [
                                ['term' => ['category_id' => (int) $product->category_id]],
                            ],
                            'must_not' => [
                                ['term' => ['id' => (int) $product->id]],
                            ],
                        ],
                    ],
                    'size' => $limit,
                ],
            ]);

            $results = $response->asArray();
            
            /** @var array<string, mixed> $hits */
            $hits = $results['hits'] ?? [];
            
            /** @var array<int, array{_id: string|int}> $hitItems */
            $hitItems = $hits['hits'] ?? [];
            
            $ids = array_map(fn ($hit) => (int) $hit['_id'], $hitItems);

            if (!empty($ids)) {
                return $ids;
            }
        } catch (\Throwable $e) {
            Log::warning('Recommendation Elasticsearch error: ' . $e->getMessage());
        }

        /** @var array<int, int> $fallbackIds */
        $fallbackIds = Product::query()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->pluck('id')
            ->all();
        return $fallbackIds;
    }

    /** 
     * @param array<int, int> $ids 
     * @return Collection<int, Product>
     */
    private function getProductsByIds(array $ids, bool $keepOrder = false): Collection
    {
        if (empty($ids)) {
            /** @var Collection<int, Product> $empty */
            $empty = collect();
            return $empty;
        }

        /** @var Collection<int, Product> $products */
        $products = Product::query()
            ->with(['category', 'images'])
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get();

        if (!$keepOrder) {
            return $products;
        }

        /** @var Collection<int, Product> $ordered */
        $ordered = collect();
        $map = $products->keyBy('id');
        foreach ($ids as $id) {
            if ($map->has($id)) {
                /** @var Product $p */
                $p = $map->get($id);
                $ordered->push($p);
            }
        }

        return $ordered;
    }

    /** @return Collection<int, Product> */
    private function getRandomProducts(int $limit): Collection
    {
        /** @var Collection<int, Product> $products */
        $products = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
        return $products;
    }

    /** 
     * @param Collection<int, Product> $products 
     * @return Collection<int, Product>
     */
    private function fillWithRandom(Collection $products, int $limit): Collection
    {
        if ($products->count() >= $limit) {
            return $products->take($limit);
        }

        $need = $limit - $products->count();
        /** @var Collection<int, Product> $extra */
        $extra = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->whereNotIn('id', $products->pluck('id'))
            ->inRandomOrder()
            ->limit($need)
            ->get();

        return $products->concat($extra);
    }

    /** 
     * @param array<int, int> $ids 
     * @return array<int, int>
     */
    private function uniqueIds(array $ids): array
    {
        /** @var array<int, int> $filtered */
        $filtered = array_values(array_unique(array_filter($ids)));
        return $filtered;
    }
}
