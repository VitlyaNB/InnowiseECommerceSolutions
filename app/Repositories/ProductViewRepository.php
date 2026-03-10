<?php

namespace App\Repositories;

use App\Models\ProductView;
use App\Repositories\Interfaces\ProductViewRepositoryInterface;

class ProductViewRepository implements ProductViewRepositoryInterface
{
    public function recordView(?int $userId, string $sessionId, int $productId): void
    {
        $identifier = $userId 
            ? ['user_id' => $userId, 'product_id' => $productId] 
            : ['session_id' => $sessionId, 'product_id' => $productId];

        ProductView::query()->updateOrCreate(
            $identifier,
            ['viewed_at' => now(), 'session_id' => $sessionId]
        );
    }

    /** @return array<int, int> */
    public function getRecentlyViewedProductIds(?int $userId, string $sessionId, int $limit = 12): array
    {
        if ($userId) {
            /** @var array<int, int> $ids */
            $ids = ProductView::query()
                ->where('user_id', $userId)
                ->orderByDesc('viewed_at')
                ->limit($limit)
                ->pluck('product_id')
                ->all();
            return $ids;
        }

        if (!empty($sessionId)) {
            /** @var array<int, int> $ids */
            $ids = ProductView::query()
                ->where('session_id', $sessionId)
                ->orderByDesc('viewed_at')
                ->limit($limit)
                ->pluck('product_id')
                ->all();
            return $ids;
        }

        return [];
    }
}
