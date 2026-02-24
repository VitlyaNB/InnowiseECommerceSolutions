<?php

namespace App\Services;

use App\DTO\CartItemDTO;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class CartService
{
    public function __construct(
        private readonly CartItemRepositoryInterface $cartItemRepository
    ) {}

    public function getUserCart(int $userId): Collection
    {
        return $this->cartItemRepository->getUserCart($userId);
    }

    public function addToCart(int $userId, CartItemDTO $data): CartItem
    {
        $existingItem = $this->cartItemRepository->findUserCartItem($userId, $data->product_id);

        if ($existingItem) {
            $this->cartItemRepository->updateQuantity($existingItem->id, $existingItem->quantity + $data->quantity);
            return $this->cartItemRepository->findById($existingItem->id);
        }

        return $this->cartItemRepository->create($userId, $data);
    }

    public function updateCartItemQuantity(int $userId, int $itemId, int $quantity): CartItem
    {
        $item = $this->cartItemRepository->findById($itemId);

        if (!$item || $item->user_id !== $userId) {
            throw new ModelNotFoundException("CartItem not found in user's cart.");
        }

        if ($quantity <= 0) {
            $this->cartItemRepository->delete($itemId);
            throw new \RuntimeException("CartItem removed because quantity is zero or less.");
        }

        $this->cartItemRepository->updateQuantity($itemId, $quantity);

        return $this->cartItemRepository->findById($itemId);
    }

    public function removeFromCart(int $userId, int $itemId): bool
    {
        $item = $this->cartItemRepository->findById($itemId);

        if (!$item || $item->user_id !== $userId) {
            throw new ModelNotFoundException("CartItem not found in user's cart.");
        }

        return $this->cartItemRepository->delete($itemId);
    }

    public function clearCart(int $userId): bool
    {
        return $this->cartItemRepository->clearUserCart($userId);
    }
}
