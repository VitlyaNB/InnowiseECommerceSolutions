<?php

namespace App\Observers;

use App\Models\ProductImage;
use App\Services\FileService;
use Illuminate\Support\Facades\Log;

class ProductImageObserver
{
    public function __construct(
        private readonly FileService $fileService
    ) {}

    public function deleted(ProductImage $productImage): void
    {
        $disk = config('filesystems.media_disk', 'public');

        try {
            $this->fileService->delete($productImage->image_path, $disk);
        } catch (\Throwable $e) {
            Log::warning('Failed to delete image from storage: ' . $e->getMessage(), [
                'path' => $productImage->image_path,
            ]);
        }
    }
}
