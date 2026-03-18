<?php

namespace App\Http\Requests;

use App\Dto\ProductFiltersDto;
use App\Dto\ProductListQueryDto;
use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'in_stock' => ['nullable', 'boolean'],
        ];
    }

    public function toDto(): ProductListQueryDto
    {
        return new ProductListQueryDto(
            filters: new ProductFiltersDto(
                categoryId: $this->has('category_id') ? (int) $this->validated('category_id') : null,
                priceMin: $this->has('price_min') ? (float) $this->validated('price_min') : null,
                priceMax: $this->has('price_max') ? (float) $this->validated('price_max') : null,
                inStock: $this->has('in_stock') ? (bool) $this->validated('in_stock') : null,
            ),
            perPage: (int) $this->validated('per_page', 15),
        );
    }
}
