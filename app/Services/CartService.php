<?php

namespace App\Services;

use App\Dto\CartDto;
use App\Dto\CartItemDto;
use App\Dto\TotalsDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;

final readonly class CartService
{
    public function __construct(
        private CartItemRepositoryInterface $cartRepository
    ) {}

    public function addToCart(CartItemDto $dto, int $userId): ?CartItemDto
    {
        $existingCartItem = $this->cartRepository->findByUserAndProduct($userId, $dto->productId);

        if ($existingCartItem) {
            if ($existingCartItem->id === null) {
                return null;
            }

            $this->cartRepository->updateQuantity($existingCartItem->id, $existingCartItem->quantity + $dto->quantity);

            return $this->cartRepository->findById($existingCartItem->id);
        }

        return $this->cartRepository->create(new CartItemDto(
            id: null,
            productId: $dto->productId,
            quantity: $dto->quantity,
            userId: $userId,
        ));
    }

    public function getCart(int $userId): CartDto
    {
        $items = $this->cartRepository->getByUser($userId);
        $itemsCount = array_reduce($items, static fn (int $carry, CartItemDto $item) => $carry + $item->quantity, 0);

        return new CartDto(
            items: $items,
            totals: $this->calculateTotals($items),
            itemsCount: $itemsCount,
        );
    }

    public function updateQuantity(int $id, int $quantity, int $userId): ?CartItemDto
    {
        $item = $this->cartRepository->findById($id);

        if (! $item || $item->userId !== $userId) {
            return null;
        }

        if ($quantity < 1) {
            $this->cartRepository->delete($id);

            return null;
        }

        $this->cartRepository->updateQuantity($id, $quantity);

        return $this->cartRepository->findById($id);
    }

    public function removeItem(int $id, int $userId): bool
    {
        $item = $this->cartRepository->findById($id);
        if (! $item || $item->userId !== $userId) {
            return false;
        }

        return $this->cartRepository->delete($id);
    }

    /**
     * @param  array<int, CartItemDto>  $items
     */
    private function calculateTotals(array $items): TotalsDto
    {
        $subtotal = array_reduce($items, function (float $carry, CartItemDto $item) {
            $price = $item->product !== null ? $item->product->price : 0.0;

            return $carry + $price * $item->quantity;
        }, 0.0);

        return new TotalsDto(
            subtotal: round($subtotal, 2),
            tax: 0.0,
            total: round($subtotal, 2),
        );
    }
}
