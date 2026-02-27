<?php

namespace App\DTO;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class CategoryDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?UploadedFile $image = null,
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        return new static(
            name: $request->validated('name'),
            image: $request->file('image'),
        );
    }
}
