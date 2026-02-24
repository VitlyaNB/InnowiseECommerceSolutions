<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function getUserOrders(int $userId): Collection
    {
        return Order::query()
            ->with('items.product')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $id): ?Order
    {
        return Order::query()
            ->with('items.product')
            ->find($id);
    }

    public function create(array $orderData): Order
    {
        return Order::create($orderData);
    }

    public function createItem(array $itemData): void
    {
        OrderItem::create($itemData);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $order = Order::query()->find($id);
        
        if (!$order) {
            return false;
        }

        return $order->update(['status' => $status]);
    }
}
