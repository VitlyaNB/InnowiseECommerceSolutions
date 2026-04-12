<?php

namespace App\Services;

use App\Dto\ProductDto;
use App\Dto\ProductIdsQueryDto;
use App\Dto\RandomProductsQueryDto;
use App\Dto\RecommendationResultDto;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductViewRepositoryInterface;
use App\Services\Interfaces\RecommendationSearcherInterface;
use RuntimeException;

final readonly class RecommendationService
{
    public function __construct(
        private ProductViewRepositoryInterface $productViewRepository,
        private ProductRepositoryInterface $productRepository,
        private RecommendationSearcherInterface $recommendationSearcher,
    ) {}

    public function recordView(?int $userId, string $sessionId, int $productId): void
    {
        if ($userId !== null) {
            $this->productViewRepository->recordViewByUser($userId, $productId, $sessionId);

            return;
        }

        $this->productViewRepository->recordViewBySession($sessionId, $productId);
    }

    public function getProductRecommendations(int $productId, ?int $userId, string $sessionId, int $limit = 8): RecommendationResultDto
    {
        $product = $this->productRepository->findById($productId);

        if (! $product) {
            throw new RuntimeException('Product not found.');
        }

        $alsoBoughtIds = $this->productRepository->getAlsoBoughtProductIds($productId, $limit);
        $similarIds = $this->getSimilarIds($product, $limit);
        $recentlyViewedIds = $this->getRecentIds($userId, $sessionId, $limit);

        return new RecommendationResultDto(
            alsoBought: $this->productRepository->getByIds(new ProductIdsQueryDto(ids: $alsoBoughtIds)),
            similar: $this->productRepository->getByIds(new ProductIdsQueryDto(ids: $similarIds)),
            recentlyViewed: $this->productRepository->getByIds(new ProductIdsQueryDto(ids: $recentlyViewedIds, keepOrder: true)),
        );
    }

    /**
     * @return array<int, ProductDto>
     */
    public function getHomeRecommendations(?int $userId, string $sessionId, int $limit = 12): array
    {
        $recentIds = $this->getRecentIds($userId, $sessionId, $limit);

        if ($recentIds === []) {
            $randomIds = $this->productRepository->getRandomActiveProductIds(new RandomProductsQueryDto(limit: $limit));

            return $this->productRepository->getByIds(new ProductIdsQueryDto(ids: $randomIds));
        }

        $seedProduct = $this->productRepository->findById($recentIds[0]);
        $similarIds = $seedProduct ? $this->getSimilarIds($seedProduct, $limit) : [];
        $mergedIds = $this->uniqueIds(array_merge($recentIds, $similarIds));

        $ids = array_slice($mergedIds, 0, $limit);
        if (count($ids) < $limit) {
            $extraIds = $this->productRepository->getRandomActiveProductIds(new RandomProductsQueryDto(
                limit: $limit - count($ids),
                excludedIds: $ids
            ));
            $ids = array_merge($ids, $extraIds);
        }

        return $this->productRepository->getByIds(new ProductIdsQueryDto(ids: $ids, keepOrder: true));
    }

    /**
     * @return array<int, int>
     */
    private function getSimilarIds(ProductDto $product, int $limit): array
    {
        $ids = $this->recommendationSearcher->searchSimilarIds($product, $limit);
        if ($ids !== []) {
            return $ids;
        }

        return $this->productRepository->getSimilarFallbackIds((int) $product->categoryId, (int) $product->id, $limit);
    }

    /**
     * @return array<int, int>
     */
    private function getRecentIds(?int $userId, string $sessionId, int $limit): array
    {
        if ($userId !== null) {
            return $this->productViewRepository->getRecentlyViewedProductIdsByUser($userId, $limit);
        }

        return $this->productViewRepository->getRecentlyViewedProductIdsBySession($sessionId, $limit);
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    private function uniqueIds(array $ids): array
    {
        /** @var array<int, int> $filtered */
        $filtered = array_values(array_unique(array_filter($ids)));

        return $filtered;
    }
}
