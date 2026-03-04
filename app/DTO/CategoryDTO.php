<?php

namespace App\DTO;

use Illuminate\Http\Request;

class CategoryDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?\Illuminate\Http\UploadedFile $image = null,
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new static(
            name: $request->input('name'),
            image: $request->file('image'),
        );
    }
}
