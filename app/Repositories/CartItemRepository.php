<?php

namespace App\Repositories;

use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\Collection;

class CartItemRepository implements CartItemRepositoryInterface
{
    /** @return Collection<int, CartItem> */
    public function getUserCart(int $userId): Collection
    {
        /** @var Collection<int, CartItem> $items */
        $items = CartItem::query()
            ->with(['product.images'])
            ->where('user_id', $userId)
            ->get();
        return $items;
    }

    /** @return Collection<int, CartItem> */
    public function getSessionCart(string $sessionId): Collection
    {
        /** @var Collection<int, CartItem> $items */
        $items = CartItem::query()
            ->with(['product.images'])
            ->where('session_id', $sessionId)
            ->get();
        return $items;
    }

    /** 
     * @param int $userId
     * @param array<int, int> $ids
     * @return Collection<int, CartItem> 
     */
    public function getSelectedItems(int $userId, array $ids): Collection
    {
        /** @var Collection<int, CartItem> $items */
        $items = CartItem::query()
            ->with(['product.images'])
            ->where('user_id', $userId)
            ->whereIn('id', $ids)
            ->get();
        return $items;
    }

    /** 
     * @param int $userId
     * @param array<int, int> $ids 
     */
    public function deleteSelectedItems(int $userId, array $ids): bool
    {
        return (bool) CartItem::query()
            ->where('user_id', $userId)
            ->whereIn('id', $ids)
            ->delete();
    }

    /** 
     * @param array<string, mixed> $identifier
     * @return Collection<int, CartItem> 
     */
    public function getCartItems(array $identifier): Collection
    {
        $query = CartItem::query()->with(['product.images']);

        if (isset($identifier['user_id'])) {
            $query->where('user_id', $identifier['user_id']);
        } elseif (isset($identifier['session_id'])) {
            $query->where('session_id', $identifier['session_id']);
        } else {
            /** @var Collection<int, CartItem> $empty */
            $empty = collect();
            return $empty;
        }

        /** @var Collection<int, CartItem> $items */
        $items = $query->get();
        return $items;
    }

    public function findById(int $id): ?CartItem
    {
        /** @var CartItem|null $item */
        $item = CartItem::with('product.images')->find($id);
        return $item;
    }

    public function findUserCartItem(int $userId, int $productId): ?CartItem
    {
        /** @var CartItem|null $item */
        $item = CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
        return $item;
    }

    public function findSessionCartItem(string $sessionId, int $productId): ?CartItem
    {
        /** @var CartItem|null $item */
        $item = CartItem::query()
            ->where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->first();
        return $item;
    }

    /** @param array<string, mixed> $identifier */
    public function findItem(array $identifier, int $productId): ?CartItem
    {
        if (isset($identifier['user_id'])) {
            /** @var int $userId */
            $userId = $identifier['user_id'];
            return $this->findUserCartItem($userId, $productId);
        }
        if (isset($identifier['session_id'])) {
            /** @var string $sessionId */
            $sessionId = $identifier['session_id'];
            return $this->findSessionCartItem($sessionId, $productId);
        }
        return null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): CartItem
    {
        return CartItem::create($data);
    }

    public function updateQuantity(int $id, int $quantity): bool
    {
        /** @var CartItem $cartItem */
        $cartItem = CartItem::query()->findOrFail($id);

        return $cartItem->update(['quantity' => $quantity]);
    }

    public function delete(int $id): bool
    {
        /** @var CartItem|null $cartItem */
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
