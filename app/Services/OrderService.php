<?php

namespace App\Services;

use App\Dto\CartItemDto;
use App\Dto\OrderDetailsDto;
use App\Dto\OrderDto;
use App\Dto\OrderItemDto;
use App\Dto\SelectedIdsDto;
use App\Dto\UserDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Jobs\SendOrderConfirmationJob;
use App\Models\User;
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

    public function createOrder(UserDto $user, OrderDto $orderDto): OrderDetailsDto
    {
        $this->transactionManager->beginTransaction();

        try {
            $selectedIdsDto = new SelectedIdsDto($orderDto->selectedItemIds);
            $cartItems = $this->cartItemRepository->getSelectedByUser($user->id, $selectedIdsDto);

            $this->validateCartItems($cartItems, $orderDto->selectedItemIds, $user->id);

            $totalAmount = $this->calculateTotalAmount($cartItems);

            $this->validateUserBalance($user, $totalAmount);

            $this->decrementUserBalance($user->id, $totalAmount);

            $order = $this->createOrderAndItems($cartItems, $user->id, $orderDto);

            $this->cartItemRepository->deleteSelectedByUser($user->id, $selectedIdsDto);

            $this->transactionManager->commit();

            SendOrderConfirmationJob::dispatch($order->id);

            return $this->orderRepository->findByIdWithItems($order->id);
        } catch (Throwable $exception) {
            $this->transactionManager->rollBack();

            throw $exception;
        }
    }

    public function createOrderFromUser(User $user, OrderDto $orderDto): OrderDetailsDto
    {
        $userDto = new UserDto(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            role: $user->role,
            balance: (float) $user->balance,
        );

        return $this->createOrder($userDto, $orderDto);
    }

    /**
     * @param  array<int, CartItemDto>  $cartItems
     */
    private function validateCartItems(array $cartItems, array $selectedIds, int $userId): void
    {
        if ($cartItems === []) {
            throw new RuntimeException('Выбранные товары (IDs: '.implode(', ', $selectedIds).") не найдены в корзине пользователя (user_id: {$userId}).");
        }

        foreach ($cartItems as $item) {
            $product = $item->product;

            if (! $product || $product->price === null || $product->quantity === null) {
                throw new RuntimeException('Товар в корзине недоступен.');
            }

            if ($product->quantity < $item->quantity) {
                throw new RuntimeException("Товара {$product->name} недостаточно на складе.");
            }
        }
    }

    /**
     * @param  array<int, CartItemDto>  $cartItems
     */
    private function calculateTotalAmount(array $cartItems): float
    {
        $totalAmount = 0.0;
        foreach ($cartItems as $item) {
            $product = $item->product;
            if ($product && $product->price !== null) {
                $totalAmount += (float) $product->price * $item->quantity;
            }
        }

        return $totalAmount;
    }

    private function validateUserBalance(UserDto $user, float $totalAmount): void
    {
        if ($user->balance < $totalAmount) {
            throw new RuntimeException('Недостаточно средств на кошельке.');
        }
    }

    private function decrementUserBalance(int $userId, float $totalAmount): void
    {
        $balanceUpdated = $this->userRepository->decrementBalance($userId, $totalAmount);
        if (! $balanceUpdated) {
            throw new RuntimeException('Не удалось списать средства.');
        }
    }

    /**
     * @param  array<int, CartItemDto>  $cartItems
     */
    private function createOrderAndItems(array $cartItems, int $userId, OrderDto $orderDto): OrderDetailsDto
    {
        $totalAmount = $this->calculateTotalAmount($cartItems);

        $order = $this->orderRepository->create(
            userId: $userId,
            totalAmount: $totalAmount,
            shippingAddress: $orderDto->shippingAddress,
            status: 'paid',
        );

        foreach ($cartItems as $item) {
            $product = $item->product;

            if (! $product || $product->price === null) {
                throw new RuntimeException('Товар в корзине недоступен.');
            }

            $stockUpdated = $this->productRepository->decrementStock($item->productId, $item->quantity);
            if (! $stockUpdated) {
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

        return $order;
    }
}
