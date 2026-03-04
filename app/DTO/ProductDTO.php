<?php

namespace App\DTO;

use Illuminate\Http\Request; // ВАЖНО: используем обычный Request

class ProductDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly float $price,
        public readonly int $quantity,
        public readonly int $category_id,
        public readonly array $images = [],
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new static(
            name: $request->validated('name'),
            description: $request->validated('description'),
            price: (float) $request->validated('price'),
            quantity: (int) $request->validated('quantity'),
            category_id: (int) $request->validated('category_id'),
            images: $request->file('images') ?? [],
        );
    }
}
