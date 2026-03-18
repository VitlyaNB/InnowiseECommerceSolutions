<?php

namespace App\Dto;

final readonly class CartDto extends BaseDto
{
    /**
     * @param array<int, CartItemDto> $items
     */
    public function __construct(
        public array $items,
        public TotalsDto $totals,
    ) {}
}
