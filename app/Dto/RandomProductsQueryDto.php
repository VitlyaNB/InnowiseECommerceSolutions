<?php

namespace App\Dto;

final readonly class RandomProductsQueryDto extends BaseDto
{
    /**
     * @param array<int, int> $excludedIds
     */
    public function __construct(
        public int $limit,
        public array $excludedIds = [],
    ) {}
}
