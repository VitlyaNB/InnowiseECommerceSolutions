<?php

namespace App\Repositories;

use App\Models\ProductView;
use App\Repositories\Interfaces\ProductViewRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductViewRepository implements ProductViewRepositoryInterface
{
    public function recordView(?int $userId, ?string $sessionId, int $productId): void
    {
        if (!$userId && !$sessionId) {
            return;
        }

        $attributes = [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'product_id' => $productId,
        ];

        ProductView::query()->updateOrCreate(
            $attributes,
            ['viewed_at' => now()]
        );
    }

    public function getRecentlyViewedProductIds(?int $userId, ?string $sessionId, int $limit = 12): array
    {
        if ($userId) {
            return ProductView::query()
                ->where('user_id', $userId)
                ->orderByDesc('viewed_at')
                ->limit($limit)
                ->pluck('product_id')
                ->all();
        }

        if ($sessionId) {
            return ProductView::query()
                ->where('session_id', $sessionId)
                ->orderByDesc('viewed_at')
                ->limit($limit)
                ->pluck('product_id')
                ->all();
        }

        return [];
    }
}
