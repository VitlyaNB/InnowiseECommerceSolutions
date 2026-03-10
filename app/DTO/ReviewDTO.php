<?php

namespace App\DTO;

use Illuminate\Http\Request;

final readonly class ReviewDTO extends BaseDTO
{
    public function __construct(
        public int $product_id = 0,
        public int $rating = 0,
        public ?string $comment = null,
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new self(
            product_id: $request->integer('product_id'),
            rating: $request->integer('rating'),
            comment: $request->has('comment') ? $request->string('comment')->value() : null,
        );
    }
}
