<?php

namespace App\Services;

use App\DTO\OrderDTO;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CartItemRepositoryInterface $cartItemRepository
    ) {}

    public function getUserOrders(int $userId): Collection
    {
        return $this->orderRepository->getUserOrders($userId);
    }

    public function getOrderById(int $userId, int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        if (!$order || $order->user_id !== $userId) {
            throw new ModelNotFoundException("Order not found or access denied.");
        }

        return $order;
    }

    public function createOrderFromCart(int $userId, OrderDTO $data): Order
    {
        $cartItems = $this->cartItemRepository->getUserCart($userId);

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException("Cannot create order from an empty cart.");
        }

        return DB::transaction(function () use ($userId, $data, $cartItems) {
            $totalAmount = 0;

            foreach ($cartItems as $item) {
                // Assuming product is loaded
                $totalAmount += $item->product->price * $item->quantity;
            }

            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'shipping_address' => $data->shipping_address,
            ]);

            foreach ($cartItems as $item) {
                $this->orderRepository->createItem([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            $this->cartItemRepository->clearUserCart($userId);

            return $order;
        });
    }

    public function updateOrderStatus(int $id, string $status): Order
    {
        $updated = $this->orderRepository->updateStatus($id, $status);

        if (!$updated) {
            throw new ModelNotFoundException("Order not found.");
        }

        return $this->orderRepository->findById($id);
    }
}
