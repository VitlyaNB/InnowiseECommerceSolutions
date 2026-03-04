<?php

namespace App\Repositories;

use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Collection;

class CartItemRepository implements CartItemRepositoryInterface
{
    public function getUserCart(int $userId): Collection
    {
        return CartItem::query()
            ->with(['product.images'])
            ->where('user_id', $userId)
            ->get();
    }

    public function getSessionCart(string $sessionId): Collection
    {
        return CartItem::query()
            ->with(['product.images'])
            ->where('session_id', $sessionId)
            ->get();
    }

    // --- РЕАЛИЗАЦИЯ НОВЫХ МЕТОДОВ ---

    public function getSelectedItems(int $userId, array $ids): Collection
    {
        return CartItem::query()
            ->with(['product.images'])
            ->where('user_id', $userId)
            ->whereIn('id', $ids) // Берем только те, что в списке ids
            ->get();
    }

    public function deleteSelectedItems(int $userId, array $ids): bool
    {
        return (bool) CartItem::query()
            ->where('user_id', $userId)
            ->whereIn('id', $ids)
            ->delete();
    }

    // --------------------------------

    public function getCartItems(array $identifier): Collection
    {
        $query = CartItem::query()->with(['product.images']);

        if (isset($identifier['user_id'])) {
            $query->where('user_id', $identifier['user_id']);
        } elseif (isset($identifier['session_id'])) {
            $query->where('session_id', $identifier['session_id']);
        } else {
            return collect();
        }

        return $query->get();
    }

    public function findById(int $id): ?CartItem
    {
        return CartItem::with('product.images')->find($id);
    }

    public function findUserCartItem(int $userId, int $productId): ?CartItem
    {
        return CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    public function findSessionCartItem(string $sessionId, int $productId): ?CartItem
    {
        return CartItem::query()
            ->where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->first();
    }

    public function findItem(array $identifier, int $productId): ?CartItem
    {
        if (isset($identifier['user_id'])) {
            return $this->findUserCartItem($identifier['user_id'], $productId);
        }
        if (isset($identifier['session_id'])) {
            return $this->findSessionCartItem($identifier['session_id'], $productId);
        }
        return null;
    }

    public function create(array $data): CartItem
    {
        return CartItem::create($data);
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

    public function clearSessionCart(string $sessionId): bool
    {
        return (bool) CartItem::query()->where('session_id', $sessionId)->delete();
    }
}
