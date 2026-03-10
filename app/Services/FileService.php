<?php

namespace App\Services;

use App\DTO\UploadImageDTO;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function upload(UploadImageDTO $dto): string
    {
        $path = Storage::disk($dto->disk)->putFile($dto->folder, $dto->file, 'public');
        return $path;
    }

    public function delete(string $pathOrUrl, string $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.media_disk', 's3');
        $path = $this->extractPathFromUrl($pathOrUrl, $disk);

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    public function getAbsoluteUrl(string $pathOrUrl, string $disk = null): string
    {
        $disk = $disk ?? config('filesystems.media_disk', 's3');
        if ($this->isAbsoluteUrl($pathOrUrl)) return $pathOrUrl;
        return Storage::disk($disk)->url($pathOrUrl);
    }

    private function isS3Disk(string $disk): bool
    {
        return config("filesystems.disks.{$disk}.driver") === 's3';
    }

    private function isAbsoluteUrl(string $value): bool
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }

    private function extractPathFromUrl(string $pathOrUrl, string $disk): string
    {
        if (!$this->isAbsoluteUrl($pathOrUrl)) return $pathOrUrl;

        $baseUrl = rtrim(config("filesystems.disks.{$disk}.url") ?? '', '/');
        if ($baseUrl && str_starts_with($pathOrUrl, $baseUrl)) {
            return ltrim(parse_url($pathOrUrl, PHP_URL_PATH), '/');
        }

        $bucket = config("filesystems.disks.{$disk}.bucket");
        if ($bucket && str_contains($pathOrUrl, "/{$bucket}/")) {
            $parts = explode("/{$bucket}/", $pathOrUrl, 2);
            return $parts[1] ?? $pathOrUrl;
        }

        return $pathOrUrl;
    }
}
