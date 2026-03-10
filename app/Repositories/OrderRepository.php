<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    /** @param array<string, mixed> $orderData */
    public function create(array $orderData): Order
    {
        return Order::create($orderData);
    }

    /** @param array<string, mixed> $itemData */
    public function createItem(array $itemData): OrderItem
    {
        return OrderItem::create($itemData);
    }
}
