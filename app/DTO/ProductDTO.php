<?php

namespace App\DTO;

class ProductDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $category_id,
        public readonly ?string $description = null,
        public readonly ?float $old_price = null,
        public readonly int $quantity = 0,
    ) {}
}
