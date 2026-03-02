<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function getUserOrders(int $userId): Collection;

    public function findById(int $id): ?Order;

    public function create(array $orderData): Order;

    public function createItem(array $itemData): void;

    public function updateStatus(int $id, string $status): bool;
}
