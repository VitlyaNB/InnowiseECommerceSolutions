<?php

namespace App\Dto;

use Illuminate\Http\UploadedFile;

final readonly class CategoryDto extends BaseDto
{
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public ?string $imagePath = null,
        public ?UploadedFile $image = null,
    ) {}
}
