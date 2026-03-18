<?php

namespace App\Dto;

final readonly class ProductSearchQueryDto extends BaseDto
{
    /**
     * @param array<int, int> $categoryIds
     */
    public function __construct(
        public string $query = '',
        public array $categoryIds = [],
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public string $sort = 'created_at_desc',
        public int $perPage = 12,
        public int $page = 1,
    ) {}
}
