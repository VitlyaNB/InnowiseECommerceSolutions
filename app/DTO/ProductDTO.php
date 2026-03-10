<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class ProductDTO extends BaseDTO
{
    /**
     * @param array<int, UploadedFile> $images
     */
    public function __construct(
        public string $name = '',
        public string $description = '',
        public float $price = 0.0,
        public ?float $old_price = null,
        public int $quantity = 0,
        public int $category_id = 0,
        public array $images = [],
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array<string, mixed> $images */
        $images = $request->file('images') ?? [];
        
        $validImages = array_filter($images, fn($img) => $img instanceof UploadedFile);

        return new self(
            name: $request->string('name')->value(),
            description: $request->string('description')->value(),
            price: (float) $request->float('price'),
            old_price: $request->has('old_price') ? $request->float('old_price') : null,
            quantity: $request->integer('quantity'),
            category_id: $request->integer('category_id'),
            images: array_values($validImages), // Ensure int keys
        );
    }
}
