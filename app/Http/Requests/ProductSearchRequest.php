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
        /** @var array<string, int|float|string|array<int, int|string>|null> $data */
        $data = $this->validated();

        /** @var string $query */
        $query = isset($data['query']) && is_string($data['query']) ? $data['query'] : '';
        /** @var array<int, int> $categoryIds */
        $categoryIds = [];
        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $catId) {
                $categoryIds[] = (int) $catId;
            }
        }
        /** @var float|null $minPrice */
        $minPrice = isset($data['min_price']) ? (float) $data['min_price'] : null;
        /** @var float|null $maxPrice */
        $maxPrice = isset($data['max_price']) ? (float) $data['max_price'] : null;
        /** @var string $sort */
        $sort = isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : 'created_at_desc';
        /** @var int $perPage */
        $perPage = (int) ($data['per_page'] ?? 12);
        /** @var int $page */
        $page = (int) ($data['page'] ?? 1);

        return new ProductSearchQueryDto(
            query: $query,
            categoryIds: $categoryIds,
            minPrice: $minPrice,
            maxPrice: $maxPrice,
            sort: $sort,
            perPage: $perPage,
            page: $page,
        );
    }
}
