<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property float|null $old_price
 * @property int $quantity
 * @property mixed $category
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'old_price' => $this->old_price ? (float) $this->old_price : null,
            'quantity' => (int) $this->quantity,
            'is_available' => $this->quantity > 0,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
