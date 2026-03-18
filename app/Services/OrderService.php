<?php

namespace App\Services;

use App\Dto\OrderDetailsDto;
use App\Dto\OrderDto;
use App\Dto\OrderItemDto;
use App\Dto\SelectedIdsDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Jobs\SendOrderConfirmationJob;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use RuntimeException;
use Throwable;

final readonly class OrderService
{
    public function __construct(
        private CartItemRepositoryInterface $cartItemRepository,
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository,
        private UserRepositoryInterface $userRepository,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function createOrder(int $userId, OrderDto $orderDto): OrderDetailsDto
    {
        $this->transactionManager->beginTransaction();

        try {
            $lockedUser = $this->userRepository->findByIdForUpdate($userId);

            if (!$lockedUser) {
                throw new RuntimeException('Пользователь не найден.');
            }

            $selectedIdsDto = new SelectedIdsDto($orderDto->selectedItemIds);
            $cartItems = $this->cartItemRepository->getSelectedByUser($userId, $selectedIdsDto);

            if ($cartItems === []) {
                throw new RuntimeException('Выбранные товары не найдены в корзине.');
            }

            $totalAmount = 0.0;
            foreach ($cartItems as $item) {
                $product = $item->product;

                if (!$product || $product->price === null || $product->quantity === null) {
                    throw new RuntimeException('Товар в корзине недоступен.');
                }

                if ($product->quantity < $item->quantity) {
                    throw new RuntimeException("Товара {$product->name} недостаточно на складе.");
                }

                $totalAmount += (float) $product->price * $item->quantity;
            }

            if ($lockedUser->balance < $totalAmount) {
                throw new RuntimeException('Недостаточно средств на кошельке.');
            }

            $balanceUpdated = $this->userRepository->decrementBalance($userId, $totalAmount);
            if (!$balanceUpdated) {
                throw new RuntimeException('Не удалось списать средства.');
            }

            $order = $this->orderRepository->create(
                userId: $userId,
                totalAmount: $totalAmount,
                shippingAddress: $orderDto->shippingAddress,
                status: 'paid',
            );

            foreach ($cartItems as $item) {
                $product = $item->product;

                if (!$product || $product->price === null) {
                    throw new RuntimeException('Товар в корзине недоступен.');
                }

                $stockUpdated = $this->productRepository->decrementStock($item->productId, $item->quantity);
                if (!$stockUpdated) {
                    throw new RuntimeException("Товара {$product->name} недостаточно на складе.");
                }

                $this->orderRepository->createItem(
                    $order->id,
                    new OrderItemDto(
                        productId: $item->productId,
                        quantity: $item->quantity,
                        price: (float) $product->price,
                    )
                );
            }

            $this->cartItemRepository->deleteSelectedByUser($userId, $selectedIdsDto);

            $this->transactionManager->commit();

            SendOrderConfirmationJob::dispatch($order->id);

            $finalOrder = $this->orderRepository->findByIdWithItems($order->id);

            if (!$finalOrder) {
                throw new RuntimeException('Не удалось загрузить заказ после создания.');
            }

            return $finalOrder;
        } catch (Throwable $exception) {
            $this->transactionManager->rollBack();

            throw $exception;
        }
    }
}
