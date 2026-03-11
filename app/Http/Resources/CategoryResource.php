<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image_path' => $this->image_path ? app(FileService::class)->getAbsoluteUrl($this->image_path) : null,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
