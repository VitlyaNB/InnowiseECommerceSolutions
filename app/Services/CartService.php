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

    public function addToCart(CartItemDto $dto, ?int $userId, ?string $sessionId): CartItemDto
    {
        $existing = null;

        if ($userId !== null) {
            $existing = $this->cartRepository->findByUserAndProduct($userId, $dto->productId);
        } elseif ($sessionId !== null) {
            $existing = $this->cartRepository->findBySessionAndProduct($sessionId, $dto->productId);
        }

        if ($existing) {
            $this->cartRepository->updateQuantity($existing->id, $existing->quantity + $dto->quantity);

            return $this->cartRepository->findById($existing->id);
        }

        return $this->cartRepository->create(new CartItemDto(
            id: 0,
            productId: $dto->productId,
            quantity: $dto->quantity,
            userId: $userId,
            sessionId: $sessionId,
        ));
    }

    public function getCart(?int $userId, ?string $sessionId): CartDto
    {
        $items = [];

        if ($userId !== null) {
            $items = $this->cartRepository->getByUser($userId);
        } elseif ($sessionId !== null) {
            $items = $this->cartRepository->getBySession($sessionId);
        }

        return new CartDto(
            items: $items,
            totals: $this->calculateTotals($items),
        );
    }

    public function updateQuantity(int $id, int $quantity, ?int $userId, ?string $sessionId): ?CartItemDto
    {
        $item = $this->cartRepository->findById($id);

        if (!$item || !$this->itemBelongsTo($item, $userId, $sessionId)) {
            return null;
        }

        if ($quantity < 1) {
            $this->cartRepository->delete($id);
            return null;
        }

        $this->cartRepository->updateQuantity($id, $quantity);
        return $this->cartRepository->findById($id);
    }

    public function removeItem(int $id, ?int $userId, ?string $sessionId): bool
    {
        $item = $this->cartRepository->findById($id);
        if (!$item || !$this->itemBelongsTo($item, $userId, $sessionId)) {
            return false;
        }
        return $this->cartRepository->delete($id);
    }

    public function clearCart(?int $userId, ?string $sessionId): bool
    {
        if ($userId) {
            return $this->cartRepository->clearByUser($userId);
        }
        if ($sessionId) {
            return $this->cartRepository->clearBySession($sessionId);
        }
        return true;
    }

    private function itemBelongsTo(CartItemDto $item, ?int $userId, ?string $sessionId): bool
    {
        if ($userId) {
            return $item->userId === $userId;
        }
        if ($sessionId) {
            return $item->sessionId === $sessionId;
        }
        return false;
    }

    /**
     * @param array<int, CartItemDto> $items
     */
    private function calculateTotals(array $items): TotalsDto
    {
        $subtotal = array_reduce($items, function (float $carry, CartItemDto $item) {
            return $carry + ($item->product?->price ?? 0.0) * $item->quantity;
        }, 0.0);

        return new TotalsDto(
            subtotal: round($subtotal, 2),
            tax: 0.0,
            total: round($subtotal, 2),
        );
    }
}
