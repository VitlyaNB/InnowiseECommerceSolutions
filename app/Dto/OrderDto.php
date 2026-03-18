<?php

namespace App\Dto;

final readonly class OrderDto extends BaseDto
{
    /**
     * @param array<int, int> $selectedItemIds
     */
    public function __construct(
        public array $selectedItemIds = [],
        public string $shippingAddress = '',
    ) {}
}
