<?php

namespace App\DTO;

use Illuminate\Http\Request; // Используем базовый Request, как в BaseDTO

class CartItemDTO extends BaseDTO
{
    public function __construct(
        public readonly int $product_id,
        public readonly int $quantity = 1,
    ) {}

    // Тип аргумента должен быть Request, чтобы совпадать с BaseDTO
    public static function fromRequest(Request $request): static
    {
        return new static(
            product_id: (int) $request->input('product_id'),
            quantity: (int) $request->input('quantity', 1),
        );
    }
}
