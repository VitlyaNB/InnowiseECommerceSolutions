<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class CategoryDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?UploadedFile $image
    ) {}

    public static function fromRequest(Request $request): self
    {
        // Используем $request->input() или $request->all(), так как это не FormRequest
        return new self(
            name: $request->input('name', ''),
            image: $request->file('image')
        );
    }
}
