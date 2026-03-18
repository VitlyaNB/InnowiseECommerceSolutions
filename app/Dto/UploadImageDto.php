<?php

namespace App\Dto;

use Illuminate\Http\UploadedFile;

final readonly class UploadImageDto extends BaseDto
{
    public function __construct(
        public UploadedFile $file,
        public string $folder = 'products',
        public string $disk = 's3',
    ) {}
}
