<?php

namespace App\Repositories\Interfaces;

use App\DTO\CartItemDTO;
use App\Models\CartItem;
use Illuminate\Support\Collection;

interface CartItemRepositoryInterface
{
    public function getUserCart(int $userId): Collection;

    public function getSessionCart(string $sessionId): Collection;

    public function getCartItems(array $identifier): Collection;

    public function findById(int $id): ?CartItem;

    public function findUserCartItem(int $userId, int $productId): ?CartItem;

    public function findSessionCartItem(string $sessionId, int $productId): ?CartItem;

    public function findItem(array $identifier, int $productId): ?CartItem;

    public function create(array $data): CartItem;

    public function updateQuantity(int $id, int $quantity): bool;

    public function delete(int $id): bool;

    public function clearUserCart(int $userId): bool;

    public function clearSessionCart(string $sessionId): bool;
}
