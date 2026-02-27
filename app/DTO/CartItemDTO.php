<?php

namespace App\DTO;

use Illuminate\Foundation\Http\FormRequest;
class CartItemDTO extends BaseDTO
{
    public function __construct(
        public readonly int $product_id,
        public readonly int $quantity = 1,
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        return new static(
            product_id: (int) $request->validated('product_id'),
            quantity: (int)$request->validated('quantity', 1),
        );
    }
}
