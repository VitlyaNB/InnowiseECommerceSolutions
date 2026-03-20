<?php

namespace App\Dto;

final readonly class ProductIdsQueryDto extends BaseDto
{
    /**
     * @param  array<int, int>  $ids
     */
    public function __construct(
        public array $ids,
        public bool $keepOrder = false,
    ) {}
}
