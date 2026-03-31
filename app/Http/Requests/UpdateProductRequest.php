<?php

namespace App\Http\Requests;

use App\Dto\ProductDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class UpdateProductRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
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
        /** @var float|null $oldPrice */
        $oldPrice = isset($data['old_price']) ? (float) $data['old_price'] : null;
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
            name: $name,
            description: $description,
            price: $price,
            oldPrice: $oldPrice,
            quantity: $quantity,
            categoryId: $categoryId,
            images: $typedImages,
        );
    }
}
