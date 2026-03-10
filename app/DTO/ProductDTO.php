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
        $validated = $request->validated();

        return new static(
            name: $validated['name'],
            description: $validated['description'] ?? '',
            price: (float) $validated['price'],
            quantity: (int) ($validated['quantity'] ?? 0),
            category_id: (int) $validated['category_id'],
            images: $request->file('images') ?? [],
        );
    }
}
