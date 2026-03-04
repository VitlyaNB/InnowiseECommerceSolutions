<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\CartItemRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    protected CartItemRepository $cartItemRepository;

    public function __construct(CartItemRepository $cartItemRepository)
    {
        $this->cartItemRepository = $cartItemRepository;
    }

    public function createOrder(User $user): Order
    {
        // Начинаем транзакцию: всё или ничего
        return DB::transaction(function () use ($user) {

            // 1. Блокируем строку пользователя для обновления (защита от двойных списаний)
            $user = User::lockForUpdate()->find($user->id);

            // 2. Получаем товары из корзины
            $cartItems = $this->cartItemRepository->getUserCart($user->id);

            if ($cartItems->isEmpty()) {
                throw new Exception('Корзина пуста');
            }

            // 3. Считаем общую сумму
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                // Предполагаем, что у товара есть цена и учитываем скидку, если есть
                $price = $item->product->price;
                $totalAmount += $price * $item->quantity;

                // Проверка склада (опционально, если нужно)
                if ($item->product->quantity < $item->quantity) {
                    throw new Exception("Товара {$item->product->name} недостаточно на складе");
                }
            }

            // 4. Проверяем баланс
            if ($user->balance < $totalAmount) {
                throw new Exception('Недостаточно средств на кошельке');
            }

            // 5. Списываем деньги
            $user->balance -= $totalAmount;
            $user->save();

            // 6. Списываем товары со склада и создаем заказ
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalAmount,
                'status' => 'paid', // Сразу ставим оплачено
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                // Уменьшаем кол-во товара на складе
                $item->product->decrement('quantity', $item->quantity);
            }

            // 7. Очищаем корзину
            $this->cartItemRepository->clearUserCart($user->id);

            return $order;
        });
    }
}
