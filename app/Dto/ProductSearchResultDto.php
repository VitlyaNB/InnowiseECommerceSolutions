<?php

namespace App\Dto;

final readonly class ProductSearchResultDto extends BaseDto
{
    /**
     * @param  array<int, ProductDto>  $data
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public array $data,
        public array $meta,
        public array $filters,
    ) {}
}
