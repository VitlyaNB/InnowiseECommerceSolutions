<?php

namespace App\Dto;

final readonly class OrderCreateDto extends BaseDto
{
    public function __construct(
        public int $userId,
        public float $totalAmount,
        public string $shippingAddress,
        public string $status = 'paid',
    ) {}
}
