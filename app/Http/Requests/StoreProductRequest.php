<?php

namespace App\Http\Requests;

use App\Dto\ProductDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,avif', 'max:20480'],
        ];
    }

    public function toDto(): ProductDto
    {
        /** @var array<string, int|float|string|null> $data */
        $data = $this->validated();

        /** @var string|null $name */
        $name = $data['name'] ?? null;
        /** @var string|null $description */
        $description = $data['description'] ?? null;
        /** @var float|null $price */
        $price = isset($data['price']) ? (float) $data['price'] : null;
        /** @var int|null $quantity */
        $quantity = isset($data['quantity']) ? (int) $data['quantity'] : null;
        /** @var int|null $categoryId */
        $categoryId = isset($data['category_id']) ? (int) $data['category_id'] : null;
        /** @var array<int|string, mixed> $images */
        $images = $this->file('images') ?? [];
        $validImages = array_filter($images, static fn ($img) => $img instanceof UploadedFile);

        /** @var array<int, UploadedFile> $typedImages */
        $typedImages = array_values($validImages);

        return new ProductDto(
            name: $name ?? '',
            description: $description,
            price: $price ?? 0.0,
            quantity: $quantity ?? 0,
            categoryId: $categoryId ?? 0,
            isActive: true,
            images: $typedImages,
        );
    }
}
