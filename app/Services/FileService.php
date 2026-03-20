<?php

namespace App\Services;

use App\Dto\UploadImageDto;
use App\Services\Interfaces\FileServiceInterface;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final readonly class FileService implements FileServiceInterface
{
    public function upload(UploadImageDto $dto): string
    {
        /** @var string|false $path */
        $path = Storage::disk($dto->disk)->putFile($dto->folder, $dto->file, 'public');

        if ($path === false) {
            throw new RuntimeException("Failed to upload file to disk {$dto->disk}");
        }

        return $path;
    }

    public function delete(string $pathOrUrl, ?string $disk = null): bool
    {
        /** @var string $actualDisk */
        $actualDisk = $disk ?? config('filesystems.media_disk', 's3');

        $path = $this->extractPathFromUrl($pathOrUrl, $actualDisk);

        if (Storage::disk($actualDisk)->exists($path)) {
            return Storage::disk($actualDisk)->delete($path);
        }

        return false;
    }

    public function getAbsoluteUrl(string $pathOrUrl, ?string $disk = null): string
    {
        /** @var string $actualDisk */
        $actualDisk = $disk ?? config('filesystems.media_disk', 's3');

        if ($this->isAbsoluteUrl($pathOrUrl)) {
            return $pathOrUrl;
        }

        /** @var string $baseUrl */
        $baseUrl = config("filesystems.disks.{$actualDisk}.url", '');
        $baseUrl = rtrim($baseUrl, '/');

        if ($baseUrl !== '') {
            return $baseUrl.'/'.$pathOrUrl;
        }

        return $pathOrUrl;
    }

    private function isAbsoluteUrl(string $value): bool
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }

    private function extractPathFromUrl(string $pathOrUrl, string $disk): string
    {
        if (! $this->isAbsoluteUrl($pathOrUrl)) {
            return $pathOrUrl;
        }

        /** @var string $rawBaseUrl */
        $rawBaseUrl = config("filesystems.disks.{$disk}.url") ?? '';
        $baseUrl = rtrim($rawBaseUrl, '/');

        if ($baseUrl && str_starts_with($pathOrUrl, $baseUrl)) {
            /** @var string|null $path */
            $path = parse_url($pathOrUrl, PHP_URL_PATH);

            return ltrim((string) $path, '/');
        }

        /** @var string|null $bucket */
        $bucket = config("filesystems.disks.{$disk}.bucket");
        if (! is_null($bucket) && str_contains($pathOrUrl, '/'.(string) $bucket.'/')) {
            $parts = explode('/'.(string) $bucket.'/', $pathOrUrl, 2);

            return $parts[1] ?? $pathOrUrl;
        }

        return $pathOrUrl;
    }
}
