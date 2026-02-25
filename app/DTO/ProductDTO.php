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

    /**
     * Статический метод для создания DTO.
     * В PHP 8.3 возвращаемый тип self и вызов new self — стандарт.
     */
    public static function fromRequest(Request $request): self
    {
        // ВАЖНО: Мы используем $request->validated(),
        // так как данные приходят после StoreProductRequest
        $validated = $request->validated();

        return new self(
            name: (string) $validated['name'],
            price: (float) $validated['price'],
            category_id: (int) $validated['category_id'],
            description: $validated['description'] ?? null,
            old_price: isset($validated['old_price']) ? (float) $validated['old_price'] : null,
            quantity: (int) ($validated['quantity'] ?? 0),
            images: $request->file('images'),
        );
    }
}
