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

    public function recordView(?int $userId, ?string $sessionId, int $productId): void
    {
        $this->productViewRepository->recordView($userId, $sessionId, $productId);
    }

    public function getProductRecommendations(int $productId, ?int $userId, ?string $sessionId, int $limit = 8): array
    {
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

    public function getHomeRecommendations(?int $userId, ?string $sessionId, int $limit = 12): Collection
    {
        $recentIds = $this->productViewRepository->getRecentlyViewedProductIds($userId, $sessionId, $limit);

        if (!empty($recentIds)) {
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

    private function getAlsoBoughtIds(int $productId, int $limit): array
    {
        return DB::table('order_items as oi')
            ->join('order_items as oi2', 'oi.order_id', '=', 'oi2.order_id')
            ->where('oi.product_id', $productId)
            ->where('oi2.product_id', '!=', $productId)
            ->select('oi2.product_id', DB::raw('COUNT(*) as freq'))
            ->groupBy('oi2.product_id')
            ->orderByDesc('freq')
            ->limit($limit)
            ->pluck('oi2.product_id')
            ->all();
    }

    private function getSimilarIds(Product $product, int $limit): array
    {
        try {
            $client = app(Client::class);

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

            $hits = $response['hits']['hits'] ?? [];
            $ids = array_map(fn ($hit) => (int) $hit['_id'], $hits);

            if (!empty($ids)) {
                return $ids;
            }
        } catch (\Throwable $e) {
            Log::warning('Recommendation Elasticsearch error: ' . $e->getMessage());
        }

        return Product::query()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->pluck('id')
            ->all();
    }

    private function getProductsByIds(array $ids, bool $keepOrder = false): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        $query = Product::query()
            ->with(['category', 'images'])
            ->whereIn('id', $ids)
            ->where('is_active', true);

        $products = $query->get();

        if (!$keepOrder) {
            return $products;
        }

        $ordered = collect();
        $map = $products->keyBy('id');
        foreach ($ids as $id) {
            if ($map->has($id)) {
                $ordered->push($map->get($id));
            }
        }

        return $ordered;
    }

    private function getRandomProducts(int $limit): Collection
    {
        return Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    private function fillWithRandom(Collection $products, int $limit): Collection
    {
        if ($products->count() >= $limit) {
            return $products->take($limit);
        }

        $need = $limit - $products->count();
        $extra = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->whereNotIn('id', $products->pluck('id'))
            ->inRandomOrder()
            ->limit($need)
            ->get();

        return $products->concat($extra);
    }

    private function uniqueIds(array $ids): array
    {
        return array_values(array_unique(array_filter($ids)));
    }
}
