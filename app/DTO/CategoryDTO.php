<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class CategoryDTO extends BaseDTO
{
    public function __construct(
        public string $name = '',
        public ?UploadedFile $image = null,
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new self(
            name: $request->string('name')->value(),
            image: $request->file('image') instanceof UploadedFile ? $request->file('image') : null,
        );
    }
}
