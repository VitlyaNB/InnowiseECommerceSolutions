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
        public ?string $name = null,
        public ?string $description = null,
        public ?float $price = null,
        public ?float $old_price = null,
        public ?int $quantity = null,
        public ?int $category_id = null,
        public array $images = [],
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array<string, mixed> $images */
        $images = $request->file('images') ?? [];
        
        $validImages = array_filter($images, fn($img) => $img instanceof UploadedFile);

        return new self(
            name: $request->has('name') ? $request->string('name')->value() : null,
            description: $request->has('description') ? $request->string('description')->value() : null,
            price: $request->has('price') ? (float) $request->float('price') : null,
            old_price: $request->has('old_price') ? $request->float('old_price') : null,
            quantity: $request->has('quantity') ? $request->integer('quantity') : null,
            category_id: $request->has('category_id') ? $request->integer('category_id') : null,
            images: array_values($validImages),
        );
    }
}
