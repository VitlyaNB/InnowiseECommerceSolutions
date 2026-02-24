<?php

namespace App\DTO;

use Illuminate\Http\Request;

class CartItemDTO extends BaseDTO
{
    public function __construct(
        public readonly int $product_id,
        public readonly int $quantity = 1,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            product_id: $request->validated('product_id'),
            quantity: $request->validated('quantity', 1),
        );
    }
}
