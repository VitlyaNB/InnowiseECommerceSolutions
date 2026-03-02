<?php

namespace App\DTO;

use Illuminate\Http\Request;

class ReviewDTO extends BaseDTO
{
    public function __construct(
        public readonly int $product_id,
        public readonly int $rating,
        public readonly ?string $comment = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            product_id: $request->validated('product_id'),
            rating: $request->validated('rating'),
            comment: $request->validated('comment'),
        );
    }
}
