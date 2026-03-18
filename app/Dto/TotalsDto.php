<?php

namespace App\Dto;

final readonly class TotalsDto extends BaseDto
{
    public function __construct(
        public float $subtotal,
        public float $tax,
        public float $total,
    ) {}
}
