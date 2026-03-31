<?php

namespace App\Http\Resources;

use App\Dto\OrderDetailsDto;
use App\Dto\OrderItemDto;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin Order
 */
#[OA\Schema(
    schema: 'OrderResource',
    title: 'Order',
    description: 'Схема ресурса заказа',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 299.99),
        new OA\Property(property: 'status', type: 'string', example: 'paid'),
        new OA\Property(property: 'shipping_address', type: 'string', example: 'г. Минск, ул. Пушкина 10'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2023-10-25 15:00:00'),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 10),
                    new OA\Property(property: 'product_id', type: 'integer', example: 5),
                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 149.99),
                    new OA\Property(
                        property: 'product',
                        type: 'object',
                        description: 'Данные товара (ProductResource)'
                    ),
                ]
            )
        ),
    ]
)]
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof OrderDetailsDto) {
            return [
                'id' => $this->resource->id,
                'total_amount' => $this->resource->totalAmount,
                'status' => $this->resource->status,
                'shipping_address' => $this->resource->shippingAddress,
                'created_at' => $this->resource->createdAt,
                'items' => array_map(static function (OrderItemDto $item): array {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->productId,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'product' => $item->product !== null ? (new ProductResource($item->product))->resolve() : null,
                    ];
                }, $this->resource->items),
            ];
        }

        return [
            'id' => $this->id,
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
            'shipping_address' => $this->shipping_address,
            'created_at' => $this->created_at->toDateTimeString(),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'product' => new ProductResource($item->product),
                ]);
            }),
        ];
    }
}
