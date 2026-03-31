<?php

namespace App\Repositories;

use App\Dto\OrderDetailsDto;
use App\Dto\OrderItemDto;
use App\Dto\ProductDto;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderRepositoryInterface;

final class OrderRepository implements OrderRepositoryInterface
{
    public function create(int $userId, float $totalAmount, string $shippingAddress, string $status = 'paid'): OrderDetailsDto
    {
        /** @var Order $order */
        $order = Order::query()->create([
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'status' => $status,
            'shipping_address' => $shippingAddress,
        ]);

        return $this->mapToDetailsDto($order);
    }

    public function createItem(int $orderId, OrderItemDto $item): void
    {
        OrderItem::query()->create([
            'order_id' => $orderId,
            'product_id' => $item->productId,
            'quantity' => $item->quantity,
            'price' => $item->price,
        ]);
    }

    public function findByIdWithItems(int $orderId): ?OrderDetailsDto
    {
        /** @var Order|null $order */
        $order = Order::query()->with(['items.product.images', 'items.product.category'])->find($orderId);

        if (! $order) {
            return null;
        }

        return $this->mapToDetailsDto($order);
    }

    /**
     * @return array<int, OrderDetailsDto>
     */
    public function getByUserId(int $userId): array
    {
        $collection = Order::query()
            ->with(['items.product.images', 'items.product.category'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        /** @var array<int, OrderDetailsDto> $result */
        $result = [];
        foreach ($collection as $order) {
            /** @var Order $order */
            $result[] = $this->mapToDetailsDto($order);
        }

        return $result;
    }

    private function mapToDetailsDto(Order $order): OrderDetailsDto
    {
        /** @var array<int, OrderItemDto> $items */
        $items = $order->relationLoaded('items')
            ? $order->items
                ->map(fn (OrderItem $item): OrderItemDto => new OrderItemDto(
                    productId: (int) $item->product_id,
                    quantity: (int) $item->quantity,
                    price: (float) $item->price,
                    id: (int) $item->id,
                    product: $item->relationLoaded('product') && $item->product !== null
                        ? new ProductDto(
                            id: (int) $item->product->id,
                            name: (string) $item->product->name,
                            description: (string) ($item->product->description ?? ''),
                            price: (float) $item->product->price,
                            quantity: (int) $item->product->quantity,
                            categoryId: (int) $item->product->category_id,
                            categoryName: $item->product->category !== null ? (string) $item->product->category->name : null,
                            images: $item->product->relationLoaded('images')
                                ? $item->product->images->pluck('image_path')->all()
                                : [],
                        )
                        : null,
                ))
                ->all()
            : [];

        return new OrderDetailsDto(
            id: (int) $order->id,
            userId: (int) $order->user_id,
            totalAmount: (float) $order->total_amount,
            status: (string) $order->status,
            shippingAddress: (string) $order->shipping_address,
            createdAt: $order->created_at !== null ? $order->created_at->toDateTimeString() : null,
            items: $items,
        );
    }
}
