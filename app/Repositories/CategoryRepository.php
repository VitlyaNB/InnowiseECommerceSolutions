<?php

namespace App\Repositories;

use App\DTO\CategoryDTO;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAll(): Collection
    {
        return Category::all();
    }

    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function create(CategoryDTO $data): Category
    {
        return Category::create($data->toArray());
    }

    public function update(int $id, CategoryDTO $data): bool
    {
        $category = Category::query()->findOrFail($id);
        return $category->update(array_filter($data->toArray(), fn($value) => $value !== null));
    }

    public function delete(int $id): bool
    {
        $category = Category::query()->find($id);
        if (!$category) {
            return false;
        }
        return (bool) $category->delete();
    }
}
