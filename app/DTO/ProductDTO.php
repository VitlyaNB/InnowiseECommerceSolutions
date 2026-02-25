<?php

namespace App\DTO;

use Illuminate\Http\Request;

class ProductDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $category_id,
        public readonly ?string $description = null,
        public readonly ?float $old_price = null,
        public readonly int $quantity = 0,
        public readonly ?array $images = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            price: $request->validated('price'),
            category_id: $request->validated('category_id'),
            description: $request->validated('description'),
            old_price: $request->validated('old_price'),
            quantity: $request->validated('quantity', 0),
            images: $request->file('images'),
        );
    }
}
