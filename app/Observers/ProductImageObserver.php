<?php

namespace App\Observers;

use App\Models\ProductImage;
use App\Services\FileService;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProductImageObserver
{
    public function __construct(
        private readonly FileService $fileService
    ) {}

    public function deleted(ProductImage $productImage): void
    {
        $diskConfig = config('filesystems.media_disk', 's3');
        /** @var string $disk */
        $disk = is_string($diskConfig) ? $diskConfig : 's3';

        try {
            $this->fileService->delete($productImage->image_path, $disk);
        } catch (Throwable $e) {
            Log::warning('Failed to delete image from storage: ' . $e->getMessage(), [
                'path' => $productImage->image_path,
            ]);
        }
    }
}
