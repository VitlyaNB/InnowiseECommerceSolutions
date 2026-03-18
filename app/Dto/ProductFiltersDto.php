<?php

namespace App\Dto;

final readonly class ProductFiltersDto extends BaseDto
{
    public function __construct(
        public ?int $categoryId = null,
        public ?float $priceMin = null,
        public ?float $priceMax = null,
        public ?bool $inStock = null,
        public ?bool $isActive = null,
    ) {}
}
