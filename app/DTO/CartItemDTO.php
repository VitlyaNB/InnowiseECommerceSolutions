<?php

namespace App\DTO;

use Illuminate\Http\Request;

final readonly class CartItemDTO extends BaseDTO
{
    public function __construct(
        public int $product_id = 0,
        public int $quantity = 1,
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new self(
            product_id: $request->integer('product_id'),
            quantity: $request->integer('quantity', 1),
        );
    }
}
