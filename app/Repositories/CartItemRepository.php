<?php

namespace App\Repositories;

use App\Dto\CartItemDto;
use App\Dto\ProductDto;
use App\Dto\SelectedIdsDto;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;

class CartItemRepository implements CartItemRepositoryInterface
{
    /** @return array<int, CartItemDto> */
    public function getByUser(int $userId): array
    {
        $collection = CartItem::query()
            ->with(['product.images'])
            ->where('user_id', $userId)
            ->get();

        /** @var array<int, CartItemDto> $result */
        $result = [];
        foreach ($collection as $item) {
            /** @var CartItem $item */
            $result[] = $this->mapToDto($item);
        }

        return $result;
    }

    /** @return array<int, CartItemDto> */
    public function getSelectedByUser(int $userId, SelectedIdsDto $ids): array
    {
        $collection = CartItem::query()
            ->with(['product.images'])
            ->where('user_id', $userId)
            ->whereIn('id', $ids->ids)
            ->get();

        /** @var array<int, CartItemDto> $result */
        $result = [];
        foreach ($collection as $item) {
            /** @var CartItem $item */
            $result[] = $this->mapToDto($item);
        }

        return $result;
    }

    public function deleteSelectedByUser(int $userId, SelectedIdsDto $ids): bool
    {
        return (bool) CartItem::query()
            ->where('user_id', $userId)
            ->whereIn('id', $ids->ids)
            ->delete();
    }

    public function findById(int $id): ?CartItemDto
    {
        /** @var CartItem|null $item */
        $item = CartItem::query()->with('product.images')->find($id);

        return $item ? $this->mapToDto($item) : null;
    }

    public function findByUserAndProduct(int $userId, int $productId): ?CartItemDto
    {
        /** @var CartItem|null $item */
        $item = CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        return $item ? $this->mapToDto($item) : null;
    }

    public function create(CartItemDto $data): CartItemDto
    {
        /** @var CartItem $item */
        $item = CartItem::query()->create([
            'product_id' => $data->productId,
            'quantity' => $data->quantity,
            'user_id' => $data->userId,
        ]);

        return $this->mapToDto($item->load('product.images'));
    }

    public function updateQuantity(int $id, int $quantity): bool
    {
        /** @var CartItem|null $cartItem */
        $cartItem = CartItem::query()->find($id);
        if (! $cartItem) {
            return false;
        }

        return $cartItem->update(['quantity' => $quantity]);
    }

    public function delete(int $id): bool
    {
        /** @var CartItem|null $cartItem */
        $cartItem = CartItem::query()->find($id);
        if (! $cartItem) {
            return false;
        }

        return (bool) $cartItem->delete();
    }

    public function clearByUser(int $userId): bool
    {
        return (bool) CartItem::query()->where('user_id', $userId)->delete();
    }

    private function mapToDto(CartItem $item): CartItemDto
    {
        $productDto = null;
        if ($item->product) {
            /** @var array<int, string> $imagePaths */
            $imagePaths = $item->product->images ? $item->product->images->pluck('image_path')->all() : [];
            $productDto = new ProductDto(
                id: $item->product->id,
                name: $item->product->name,
                price: (float) $item->product->price,
                quantity: (int) $item->product->quantity,
                images: $imagePaths,
            );
        }

        return new CartItemDto(
            id: $item->id,
            productId: $item->product_id,
            quantity: $item->quantity,
            userId: $item->user_id,
            product: $productDto,
        );
    }
}
