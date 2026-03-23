<?php

namespace App\Dto;

final readonly class CartItemDto extends BaseDto
{
    public function __construct(
        public ?int $id,
        public int $productId,
        public int $quantity,
        public ?int $userId = null,
        public ?string $sessionId = null,
        public ?ProductDto $product = null,
    ) {}
}
