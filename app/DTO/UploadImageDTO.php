<?php

namespace App\DTO;

use Illuminate\Http\UploadedFile;

readonly class UploadImageDTO
{
    public function __construct(
        public UploadedFile $file,
        public string $folder = 'products',
        public string $disk = 's3'
    ) {}
}
