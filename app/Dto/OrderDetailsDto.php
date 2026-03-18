<?php

namespace App\Dto;

final readonly class OrderDetailsDto extends BaseDto
{
    /**
     * @param array<int, OrderItemDto> $items
     */
    public function __construct(
        public int $id,
        public int $userId,
        public float $totalAmount,
        public string $status,
        public string $shippingAddress,
        public ?string $createdAt = null,
        public array $items = [],
    ) {}
}
