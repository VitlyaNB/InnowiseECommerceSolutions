<?php

namespace App\Repositories\Interfaces;

interface ProductViewRepositoryInterface
{
    public function recordViewByUser(int $userId, int $productId, string $sessionId): void;

    public function recordViewBySession(string $sessionId, int $productId): void;

    /** @return array<int, int> */
    public function getRecentlyViewedProductIdsByUser(int $userId, int $limit = 10): array;

    /** @return array<int, int> */
    public function getRecentlyViewedProductIdsBySession(string $sessionId, int $limit = 10): array;
}
