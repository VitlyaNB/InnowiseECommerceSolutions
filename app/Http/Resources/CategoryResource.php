<?php

namespace App\Http\Resources;

use App\Dto\CategoryDto;
use App\Models\Category;
use App\Services\Interfaces\FileServiceInterface;
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
        if ($this->resource instanceof CategoryDto) {
            return [
                'id' => $this->resource->id,
                'name' => $this->resource->name,
                'image_url' => $this->resource->imagePath
                    ? app(FileServiceInterface::class)->getAbsoluteUrl($this->resource->imagePath)
                    : null,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image_url' => $this->image_path
                ? app(FileServiceInterface::class)->getAbsoluteUrl($this->image_path)
                : null,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
