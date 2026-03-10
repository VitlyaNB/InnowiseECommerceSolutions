<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\CartItemRepository;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Jobs\SendOrderConfirmationJob;

class OrderService
{
    protected CartItemRepository $cartItemRepository;

    public function __construct(CartItemRepository $cartItemRepository)
    {
        $this->cartItemRepository = $cartItemRepository;
    }

    public function createOrder(User $user, array $selectedItemIds): Order
    {
        return DB::transaction(function () use ($user, $selectedItemIds) {

            $user = User::lockForUpdate()->find($user->id);

            $cartItems = $this->cartItemRepository->getSelectedItems($user->id, $selectedItemIds);

            if ($cartItems->isEmpty()) {
                throw new Exception('Выбранные товары не найдены в корзине.');
            }

            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item->product->price * $item->quantity;

                if ($item->product->quantity < $item->quantity) {
                    throw new Exception("Товара {$item->product->name} недостаточно на складе.");
                }
            }

            if ($user->balance < $totalAmount) {
                throw new Exception('Недостаточно средств на кошельке.');
            }

            $user->balance -= $totalAmount;
            $user->save();

            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'paid',
                'shipping_address' => 'Адрес не указан',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('quantity', $item->quantity);
            }

            $this->cartItemRepository->deleteSelectedItems($user->id, $selectedItemIds);

            DB::afterCommit(function () use ($order) {
                SendOrderConfirmationJob::dispatch($order->id);
            });

            return $order;
        });
    }
}
