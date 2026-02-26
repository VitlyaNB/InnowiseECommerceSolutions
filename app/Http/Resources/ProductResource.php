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
            'category' => new CategoryResource($this->whenLoaded('category')),
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $this->resolveImageUrl($image->image_path),
                    ];
                });
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function resolveImageUrl(string $pathOrUrl): string
    {
        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            return $pathOrUrl;
        }

        $disk = config('filesystems.media_disk', 'public');

        return $disk === 's3'
            ? Storage::disk('s3')->url($pathOrUrl)
            : asset('storage/' . $pathOrUrl);
    }
}
