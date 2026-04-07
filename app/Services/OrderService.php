<?php

namespace App\Services;

use App\Dto\CartItemDto;
use App\Dto\OrderCreateDto;
use App\Dto\OrderDetailsDto;
use App\Dto\OrderDto;
use App\Dto\OrderItemDto;
use App\Dto\SelectedIdsDto;
use App\Dto\UserDto;
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

            $savedOrder = $this->orderRepository->findByIdWithItems($order->id);
            if ($savedOrder === null) {
                throw new RuntimeException('Failed to load the created order.');
            }

            return $savedOrder;
        } catch (Throwable $exception) {
            $this->transactionManager->rollBack();

            throw $exception;
        }
    }

    /**
     * @param  array<int, CartItemDto>  $cartItems
     * @param  array<int, int>  $selectedIds
     */
    private function validateCartItems(array $cartItems, array $selectedIds, int $userId): void
    {
        if ($cartItems === []) {
            throw new RuntimeException('Selected items (IDs: '.implode(', ', $selectedIds).") were not found in the user cart (user_id: {$userId}).");
        }

        $selectedUniqueIds = array_values(array_unique(array_map(static fn (int|string $id): int => (int) $id, $selectedIds)));
        $foundIds = [];
        foreach ($cartItems as $item) {
            if ($item->id !== null) {
                $foundIds[] = (int) $item->id;
            }
        }
        $missingIds = array_values(array_diff($selectedUniqueIds, $foundIds));
        if ($missingIds !== []) {
            throw new RuntimeException('Some selected items are not available in the user cart (IDs: '.implode(', ', $missingIds).').');
        }

        foreach ($cartItems as $item) {
            $product = $item->product;

            if (! $product || $product->price === null || $product->quantity === null) {
                throw new RuntimeException('A cart item product is unavailable.');
            }

            if ($product->quantity < $item->quantity) {
                throw new RuntimeException("Not enough stock for {$product->name}.");
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
            throw new RuntimeException('Insufficient wallet balance.');
        }
    }

    private function decrementUserBalance(int $userId, float $totalAmount): void
    {
        $balanceUpdated = $this->userRepository->decrementBalance($userId, $totalAmount);
        if (! $balanceUpdated) {
            throw new RuntimeException('Failed to deduct funds.');
        }
    }

    /**
     * @param  array<int, CartItemDto>  $cartItems
     */
    private function createOrderAndItems(array $cartItems, int $userId, OrderDto $orderDto): OrderDetailsDto
    {
        $totalAmount = $this->calculateTotalAmount($cartItems);

        $order = $this->orderRepository->create(new OrderCreateDto(
            userId: $userId,
            totalAmount: $totalAmount,
            shippingAddress: $orderDto->shippingAddress,
            status: 'paid',
        ));

        foreach ($cartItems as $item) {
            $product = $item->product;

            if (! $product || $product->price === null) {
                throw new RuntimeException('A cart item product is unavailable.');
            }

            $stockUpdated = $this->productRepository->decrementStock($item->productId, $item->quantity);
            if (! $stockUpdated) {
                throw new RuntimeException("Not enough stock for {$product->name}.");
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
