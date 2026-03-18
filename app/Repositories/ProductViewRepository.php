<?php

namespace App\Repositories;

use App\Models\ProductView;
use App\Repositories\Interfaces\ProductViewRepositoryInterface;

class ProductViewRepository implements ProductViewRepositoryInterface
{
    public function recordViewByUser(int $userId, int $productId, string $sessionId): void
    {
        ProductView::query()->updateOrCreate(
            ['user_id' => $userId, 'product_id' => $productId],
            ['viewed_at' => now(), 'session_id' => $sessionId]
        );
    }

    public function recordViewBySession(string $sessionId, int $productId): void
    {
        ProductView::query()->updateOrCreate(
            ['session_id' => $sessionId, 'product_id' => $productId],
            ['viewed_at' => now()]
        );
    }

    /** @return array<int, int> */
    public function getRecentlyViewedProductIdsByUser(int $userId, int $limit = 12): array
    {
        /** @var array<int, int> $ids */
        $ids = ProductView::query()
            ->where('user_id', $userId)
            ->orderByDesc('viewed_at')
            ->limit($limit)
            ->pluck('product_id')
            ->all();

        return $ids;
    }

    /** @return array<int, int> */
    public function getRecentlyViewedProductIdsBySession(string $sessionId, int $limit = 12): array
    {
        /** @var array<int, int> $ids */
        $ids = ProductView::query()
            ->where('session_id', $sessionId)
            ->orderByDesc('viewed_at')
            ->limit($limit)
            ->pluck('product_id')
            ->all();

        return $ids;
    }
}
