<?php

namespace App\Repositories\Interfaces;

use App\DTO\CartItemDTO;
use App\Models\CartItem;
use Illuminate\Support\Collection;

interface CartItemRepositoryInterface
{
    public function getUserCart(int $userId): Collection;

    public function findById(int $id): ?CartItem;

    public function findUserCartItem(int $userId, int $productId): ?CartItem;

    public function create(int $userId, CartItemDTO $data): CartItem;

    public function updateQuantity(int $id, int $quantity): bool;

    public function delete(int $id): bool;

    public function clearUserCart(int $userId): bool;
}
