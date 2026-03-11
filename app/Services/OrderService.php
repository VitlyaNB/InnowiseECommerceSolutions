<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Jobs\SendOrderConfirmationJob;

class OrderService
{
    protected CartItemRepositoryInterface $cartItemRepository;

    public function __construct(CartItemRepositoryInterface $cartItemRepository)
    {
        $this->cartItemRepository = $cartItemRepository;
    }

    /** @param array<int, int> $selectedItemIds */
    public function createOrder(User $user, array $selectedItemIds, string $shippingAddress): Order
    {
        /** @var Order $finalOrder */
        $finalOrder = DB::transaction(function () use ($user, $selectedItemIds, $shippingAddress) {

            /** @var User $lockedUser */
            $lockedUser = User::query()->lockForUpdate()->find($user->id) ?? $user;

            $cartItems = $this->cartItemRepository->getSelectedItems($lockedUser->id, $selectedItemIds);

            if ($cartItems->isEmpty()) {
                throw new Exception('Выбранные товары не найдены в корзине.');
            }

            $totalAmount = 0.0;
            /** @var CartItem $item */
            foreach ($cartItems as $item) {
                $totalAmount += (float) $item->product->price * $item->quantity;

                if ($item->product->quantity < $item->quantity) {
                    throw new Exception("Товара {$item->product->name} недостаточно на складе.");
                }
            }

            if ($lockedUser->balance < $totalAmount) {
                throw new Exception('Недостаточно средств на кошельке.');
            }

            $lockedUser->balance -= $totalAmount;
            $lockedUser->save();

            $order = Order::create([
                'user_id' => $lockedUser->id,
                'total_amount' => $totalAmount,
                'status' => 'paid',
                'shipping_address' => $shippingAddress,
            ]);

            /** @var CartItem $item */
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                \App\Models\Product::query()->where('id', $item->product_id)->decrement('quantity', $item->quantity);
            }

            $this->cartItemRepository->deleteSelectedItems($lockedUser->id, $selectedItemIds);

            DB::afterCommit(function () use ($order) {
                SendOrderConfirmationJob::dispatch($order);
            });

            return $order;
        });
        
        return $finalOrder;
    }
}
