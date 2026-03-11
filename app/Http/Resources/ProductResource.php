<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category_name' => $this->category !== null ? $this->category->name : '',
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'old_price' => $this->old_price ? (float) $this->old_price : null,
            'quantity' => (int) $this->quantity,
            'images' => $this->images->map(fn($img) => [
                'id' => $img->id,
                'url' => app(FileService::class)->getAbsoluteUrl($img->image_path)
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
