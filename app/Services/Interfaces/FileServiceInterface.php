<?php

namespace App\Services\Interfaces;

use App\Dto\UploadImageDto;

interface FileServiceInterface
{
    public function upload(UploadImageDto $dto): string;

    public function delete(string $pathOrUrl, ?string $disk = null): bool;

    public function getAbsoluteUrl(string $pathOrUrl, ?string $disk = null): string;
}
