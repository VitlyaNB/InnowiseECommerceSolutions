<?php

namespace App\Http\Resources;

use App\Dto\CartItemDto;
use App\Models\CartItem;
use App\Services\Interfaces\FileServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin CartItem
 */
#[OA\Schema(
    schema: 'CartResource',
    description: 'Cart item resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'product_id', type: 'integer', example: 5),
        new OA\Property(property: 'quantity', type: 'integer', example: 2),
        new OA\Property(property: 'product', ref: '#/components/schemas/ProductResource'),
    ]
)]
class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof CartItemDto) {
            $productData = $this->resource->product?->toArray() ?? [];
            if ($this->resource->product !== null && ! empty($this->resource->product->images)) {
                $fileService = app(FileServiceInterface::class);
                $imageUrls = [];
                foreach ($this->resource->product->images as $path) {
                    if (is_string($path)) {
                        $imageUrls[] = ['id' => null, 'url' => $fileService->getAbsoluteUrl($path)];
                    }
                }
                $productData['images'] = $imageUrls;
            }

            return [
                'id' => $this->resource->id,
                'product_id' => $this->resource->productId,
                'quantity' => $this->resource->quantity,
                'product' => $productData,
            ];
        }

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
