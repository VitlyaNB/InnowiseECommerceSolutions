<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\FileService;

class ProductObserver
{
    public function __construct(
        private readonly FileService $fileService
    ) {}

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        // When a product is deleted, delete its images from S3
        foreach ($product->images as $image) {
            $this->fileService->delete($image->image_path);
            // Optionally delete the model if no cascade on delete is set, but deleting product will usually cascade delete images.
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Any specific cleanup for updated product if needed
    }
}
