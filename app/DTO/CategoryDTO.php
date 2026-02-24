<?php

namespace App\DTO;

use Illuminate\Http\Request;

class CategoryDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
        );
    }
}
