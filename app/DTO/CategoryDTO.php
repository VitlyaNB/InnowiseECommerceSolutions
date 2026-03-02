<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class CategoryDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?UploadedFile $image // Важно: тип UploadedFile или null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            // Проверяем, есть ли файл в поле 'image'
            image: $request->file('image')
        );
    }
}
