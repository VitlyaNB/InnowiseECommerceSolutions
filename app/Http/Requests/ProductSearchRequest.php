<?php

namespace App\Http\Requests;

use App\Dto\ProductSearchQueryDto;
use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
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
            'query' => ['nullable', 'string'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'string', 'in:created_at_desc,price_asc,price_desc,name_asc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toDto(): ProductSearchQueryDto
    {
        /** @var array<int, int> $categoryIds */
        $categoryIds = $this->validated('categories', []);

        return new ProductSearchQueryDto(
            query: (string) $this->validated('query', ''),
            categoryIds: $categoryIds,
            minPrice: $this->has('min_price') ? (float) $this->validated('min_price') : null,
            maxPrice: $this->has('max_price') ? (float) $this->validated('max_price') : null,
            sort: (string) $this->validated('sort', 'created_at_desc'),
            perPage: (int) $this->validated('per_page', 12),
            page: (int) $this->validated('page', 1),
        );
    }
}
