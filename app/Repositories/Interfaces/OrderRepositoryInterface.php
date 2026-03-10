<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use App\Models\OrderItem;

interface OrderRepositoryInterface
{
    /** @param array<string, mixed> $orderData */
    public function create(array $orderData): Order;

    /** @param array<string, mixed> $itemData */
    public function createItem(array $itemData): OrderItem;
}
