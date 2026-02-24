<?php

namespace App\Repositories;

use App\DTO\CartItemDTO;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Collection;

class CartItemRepository implements CartItemRepositoryInterface
{
    public function getUserCart(int $userId): Collection
    {
        return CartItem::query()
            ->with('product')
            ->where('user_id', $userId)
            ->get();
    }

    public function findById(int $id): ?CartItem
    {
        return CartItem::find($id);
    }

    public function findUserCartItem(int $userId, int $productId): ?CartItem
    {
        return CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    public function create(int $userId, CartItemDTO $data): CartItem
    {
        return CartItem::create([
            'user_id' => $userId,
            'product_id' => $data->product_id,
            'quantity' => $data->quantity,
        ]);
    }

    public function updateQuantity(int $id, int $quantity): bool
    {
        $cartItem = CartItem::query()->findOrFail($id);
        
        return $cartItem->update(['quantity' => $quantity]);
    }

    public function delete(int $id): bool
    {
        $cartItem = CartItem::query()->find($id);
        
        if (!$cartItem) {
            return false;
        }

        return (bool) $cartItem->delete();
    }

    public function clearUserCart(int $userId): bool
    {
        return (bool) CartItem::query()->where('user_id', $userId)->delete();
    }
}
