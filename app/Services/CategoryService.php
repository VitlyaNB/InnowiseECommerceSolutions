<?php

namespace App\Services;

use App\DTO\CategoryDTO;
use App\DTO\UploadImageDTO;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

readonly class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private FileService                 $fileService
    ) {}

    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    public function getCategoryById(int $id): Category
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new ModelNotFoundException("Category with ID {$id} not found.");
        }

        return $category;
    }

    public function createCategory(CategoryDTO $data): Category
    {
        $categoryData = ['name' => $data->name];
        if ($data->image) {
            $categoryData['image_path'] = $this->fileService->upload(new UploadImageDTO(
                file: $data->image,
                folder: 'categories',
                disk: config('filesystems.media_disk', 's3')
            ));
        }

        return $this->categoryRepository->create($categoryData);
    }

    public function updateCategory(int $id, CategoryDTO $data): Category
    {
        $category = $this->getCategoryById($id);
        $updateData = ['name' => $data->name];

        if ($data->image) {
            if ($category->image_path) {
                $this->fileService->delete($category->image_path);
            }

            $updateData['image_path'] = $this->fileService->upload(new UploadImageDTO(
                file: $data->image,
                folder: 'categories'
            ));
        }

        $this->categoryRepository->update($id, $updateData);

        return $this->getCategoryById($id);
    }

    public function deleteCategory(int $id): bool
    {
        $category = $this->getCategoryById($id);

        if ($category->image_path) {
            $this->fileService->delete($category->image_path);
        }

        return $this->categoryRepository->delete($id);
    }
}
