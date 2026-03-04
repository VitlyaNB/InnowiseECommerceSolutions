<?php

namespace App\DTO;

use Illuminate\Http\Request;

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
        // Заменяем все validated(...) на input(...)
        return new static(
            name: $request->input('name'),
            description: $request->input('description'),
            price: (float) $request->input('price'),
            quantity: (int) $request->input('quantity'),
            category_id: (int) $request->input('category_id'),
            images: $request->file('images') ?? [],
        );
    }
}
