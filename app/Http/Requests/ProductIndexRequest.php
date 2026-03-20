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
        /** @var array<string, int|float|bool|null> $data */
        $data = $this->validated();

        /** @var int|null $categoryId */
        $categoryId = isset($data['category_id']) ? (int) $data['category_id'] : null;
        /** @var float|null $priceMin */
        $priceMin = isset($data['price_min']) ? (float) $data['price_min'] : null;
        /** @var float|null $priceMax */
        $priceMax = isset($data['price_max']) ? (float) $data['price_max'] : null;
        /** @var bool|null $inStock */
        $inStock = isset($data['in_stock']) ? (bool) $data['in_stock'] : null;
        /** @var int $perPage */
        $perPage = (int) ($data['per_page'] ?? 15);

        return new ProductListQueryDto(
            filters: new ProductFiltersDto(
                categoryId: $categoryId,
                priceMin: $priceMin,
                priceMax: $priceMax,
                inStock: $inStock,
            ),
            perPage: $perPage,
        );
    }
}
