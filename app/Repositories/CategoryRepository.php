<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    /** @return Collection<int, Category> */
    public function getAll(): Collection
    {
        /** @var Collection<int, Category> $categories */
        $categories = Category::query()->get();
        return $categories;
    }

    public function findById(int $id): ?Category
    {
        /** @var Category|null $category */
        $category = Category::query()->find($id);
        return $category;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Category
    {
        /** @var Category $category */
        $category = Category::query()->findOrFail($id);
        $category->update($data);
        return $category;
    }

    public function delete(int $id): bool
    {
        /** @var Category|null $category */
        $category = Category::query()->find($id);
        if (!$category) {
            return false;
        }
        return (bool) $category->delete();
    }
}
