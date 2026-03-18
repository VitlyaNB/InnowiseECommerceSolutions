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
        /** @var array<string, mixed> $images */
        $images = $this->file('images') ?? [];
        $validImages = array_filter($images, fn($img) => $img instanceof UploadedFile);

        return new ProductDto(
            name: $this->validated('name'),
            description: $this->validated('description'),
            price: $this->has('price') ? (float) $this->validated('price') : null,
            oldPrice: $this->has('old_price') ? (float) $this->validated('old_price') : null,
            quantity: $this->has('quantity') ? (int) $this->validated('quantity') : null,
            categoryId: $this->has('category_id') ? (int) $this->validated('category_id') : null,
            images: array_values($validImages),
        );
    }
}
