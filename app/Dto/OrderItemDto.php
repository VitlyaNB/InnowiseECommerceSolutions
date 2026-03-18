<?php

namespace App\Dto;

final readonly class OrderItemDto extends BaseDto
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public float $price,
    ) {}
}
