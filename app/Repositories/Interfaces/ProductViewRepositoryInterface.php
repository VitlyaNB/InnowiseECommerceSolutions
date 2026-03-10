<?php

namespace App\Repositories\Interfaces;

interface ProductViewRepositoryInterface
{
    public function recordView(?int $userId, string $sessionId, int $productId): void;

    /** @return array<int, int> */
    public function getRecentlyViewedProductIds(?int $userId, string $sessionId, int $limit = 10): array;
}
