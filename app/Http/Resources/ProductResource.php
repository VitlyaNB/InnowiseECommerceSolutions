<?php

namespace App\Http\Resources;

use App\Dto\ProductDto;
use App\Models\Product;
use App\Services\Interfaces\FileServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin Product
 */
#[OA\Schema(
    schema: 'ProductResource',
    description: 'Product resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'category_id', type: 'integer', example: 2),
        new OA\Property(property: 'category_name', type: 'string', example: 'Electronics'),
        new OA\Property(property: 'name', type: 'string', example: 'iPhone 15 Pro'),
        new OA\Property(property: 'description', type: 'string', example: 'The latest Apple flagship phone.'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 999.99),
        new OA\Property(property: 'quantity', type: 'integer', example: 50),
        new OA\Property(
            property: 'images',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'url', type: 'string', format: 'uri', example: 'https://cdn.example.com/products/iphone15.jpg'),
                ]
            )
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof ProductDto) {
            $fileService = app(FileServiceInterface::class);

            $imageUrls = [];
            foreach ($this->resource->images as $image) {
                if (is_string($image)) {
                    $imageUrls[] = ['id' => null, 'url' => $fileService->getAbsoluteUrl($image)];
                }
            }

            return [
                'id' => $this->resource->id,
                'category_id' => $this->resource->categoryId,
                'category_name' => $this->resource->categoryName ?? '',
                'name' => $this->resource->name,
                'description' => $this->resource->description,
                'price' => $this->resource->price,
                'quantity' => $this->resource->quantity,
                'images' => $imageUrls,
                'created_at' => null,
            ];
        }

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category_name' => $this->category !== null ? $this->category->name : '',
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'quantity' => (int) $this->quantity,
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => app(FileServiceInterface::class)->getAbsoluteUrl($img->image_path),
            ])),
            'created_at' => $this->created_at,
        ];
    }
}
