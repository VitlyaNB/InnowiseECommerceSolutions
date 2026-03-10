<?php

namespace App\Repositories\Interfaces;

use App\Models\CartItem;
use Illuminate\Support\Collection;

interface CartItemRepositoryInterface
{
    /** @return Collection<int, CartItem> */
    public function getUserCart(int $userId): Collection;

    /** @return Collection<int, CartItem> */
    public function getSessionCart(string $sessionId): Collection;

    /** 
     * @param array<string, mixed> $identifier
     * @return Collection<int, CartItem> 
     */
    public function getCartItems(array $identifier): Collection;

    /** 
     * @param array<int, int> $ids
     * @return Collection<int, CartItem> 
     */
    public function getSelectedItems(int $userId, array $ids): Collection;

    /** @param array<int, int> $ids */
    public function deleteSelectedItems(int $userId, array $ids): bool;

    public function findById(int $id): ?CartItem;

    public function findUserCartItem(int $userId, int $productId): ?CartItem;

    public function findSessionCartItem(string $sessionId, int $productId): ?CartItem;

    /** @param array<string, mixed> $identifier */
    public function findItem(array $identifier, int $productId): ?CartItem;

    /** @param array<string, mixed> $data */
    public function create(array $data): CartItem;

    public function updateQuantity(int $id, int $quantity): bool;

    public function delete(int $id): bool;

    public function clearUserCart(int $userId): bool;

    public function clearSessionCart(string $sessionId): bool;
}
