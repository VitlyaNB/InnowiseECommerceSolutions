<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\Interfaces\FileServiceInterface;

class CategoryObserver
{
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    public function deleted(Category $category): void
    {
        if ($category->image_path) {
            $this->fileService->delete($category->image_path);
        }
    }
}
