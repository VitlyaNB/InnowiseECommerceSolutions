<?php

namespace App\Http\Resources;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CartItem
 */
class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
