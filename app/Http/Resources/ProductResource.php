<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),

            'images' => $this->relationLoaded('images')
                ? $this->images->map(fn($img) => [
                    'id' => $img->id,
                    'url' => $this->resolveImageUrl($img->image_path),
                ])
                : [],

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function resolveImageUrl(?string $pathOrUrl): ?string
    {
        if (!$pathOrUrl) return null;

        if (str_starts_with($pathOrUrl, 'http')) {
            return $pathOrUrl;
        }

        $diskName = config('filesystems.media_disk', 's3');

        return Storage::disk($diskName)->url($pathOrUrl);
    }
}
