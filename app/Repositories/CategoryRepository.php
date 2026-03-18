<?php

namespace App\Repositories;

use App\Dto\CategoryDto;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    /** @return array<int, CategoryDto> */
    public function getAll(): array
    {
        return Category::query()
            ->get()
            ->map(fn(Category $category) => $this->mapToDto($category))
            ->toArray();
    }

    public function findById(int $id): ?CategoryDto
    {
        /** @var Category|null $category */
        $category = Category::query()->find($id);

        return $category ? $this->mapToDto($category) : null;
    }

    public function create(CategoryDto $data): CategoryDto
    {
        /** @var Category $category */
        $category = Category::create([
            'name' => $data->name,
            'image_path' => $data->imagePath,
        ]);

        return $this->mapToDto($category);
    }

    public function update(int $id, CategoryDto $data): bool
    {
        /** @var Category|null $category */
        $category = Category::query()->find($id);

        if (!$category) {
            return false;
        }

        $updateData = array_filter([
            'name' => $data->name,
            'image_path' => $data->imagePath,
        ], fn($value) => !is_null($value));

        return $category->update($updateData);
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

    public function existsByName(string $name): bool
    {
        return Category::query()
            ->where('name', $name)
            ->exists();
    }

    private function mapToDto(Category $category): CategoryDto
    {
        return new CategoryDto(
            id: $category->id,
            name: $category->name,
            imagePath: $category->image_path,
        );
    }
}
