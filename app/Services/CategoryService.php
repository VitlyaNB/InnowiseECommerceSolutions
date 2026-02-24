<?php

namespace App\Services;

use App\DTO\CategoryDTO;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
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
        return $this->categoryRepository->create($data);
    }

    public function updateCategory(int $id, CategoryDTO $data): Category
    {
        $updated = $this->categoryRepository->update($id, $data);

        if (!$updated) {
            throw new \RuntimeException("Failed to update category with ID {$id}.");
        }

        return $this->getCategoryById($id);
    }

    public function deleteCategory(int $id): bool
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new ModelNotFoundException("Category with ID {$id} not found.");
        }

        return $this->categoryRepository->delete($id);
    }
}
