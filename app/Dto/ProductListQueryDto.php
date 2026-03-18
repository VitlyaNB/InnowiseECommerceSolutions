<?php

namespace App\Dto;

final readonly class ProductListQueryDto extends BaseDto
{
    public function __construct(
        public ProductFiltersDto $filters,
        public int $perPage = 15,
    ) {}
}
