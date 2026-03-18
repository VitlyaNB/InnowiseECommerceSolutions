<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\FileService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    public function __construct(
        private readonly FileService $fileService
    ) {}

    public function created(Product $product): void
    {
        Log::info("Product created: {$product->name}");
    }

    public function deleted(Product $product): void
    {
        foreach ($product->images as $image) {
            $this->fileService->delete($image->image_path);
        }
    }
}
