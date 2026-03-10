<?php

namespace App\Repositories\Interfaces;

interface ProductViewRepositoryInterface
{
    public function recordView(?int $userId, ?string $sessionId, int $productId): void;
    public function getRecentlyViewedProductIds(?int $userId, ?string $sessionId, int $limit = 12): array;
}
