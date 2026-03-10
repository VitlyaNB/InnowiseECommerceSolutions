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

    // Принимаем массив ID товаров, которые выбрал пользователь
    public function createOrder(User $user, array $selectedItemIds): Order
    {
        return DB::transaction(function () use ($user, $selectedItemIds) {

            // 1. Блокируем пользователя (защита баланса)
            $user = User::lockForUpdate()->find($user->id);

            // 2. Получаем ТОЛЬКО выбранные товары
            $cartItems = $this->cartItemRepository->getSelectedItems($user->id, $selectedItemIds);

            if ($cartItems->isEmpty()) {
                throw new Exception('Выбранные товары не найдены в корзине.');
            }

            // 3. Считаем сумму
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item->product->price * $item->quantity;

                if ($item->product->quantity < $item->quantity) {
                    throw new Exception("Товара {$item->product->name} недостаточно на складе.");
                }
            }

            // 4. Проверяем баланс
            if ($user->balance < $totalAmount) {
                throw new Exception('Недостаточно средств на кошельке.');
            }

            // 5. Списываем деньги
            $user->balance -= $totalAmount;
            $user->save();

            // 6. Создаем заказ (ИСПРАВЛЕНА ОШИБКА ЗДЕСЬ)
            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount, // Было total_price, стало total_amount
                'status' => 'paid',
                'shipping_address' => 'Адрес не указан', // Обязательное поле в БД
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

            // 7. Удаляем из корзины только купленное
            $this->cartItemRepository->deleteSelectedItems($user->id, $selectedItemIds);

            DB::afterCommit(function () use ($order) {
                SendOrderConfirmationJob::dispatch($order->id);
            });

            return $order;
        });
    }
}
