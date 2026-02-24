<?php

namespace App\Repositories\Interfaces;

use App\DTO\CategoryDTO;
use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Category;

    public function create(CategoryDTO $data): Category;

    public function update(int $id, CategoryDTO $data): bool;

    public function delete(int $id): bool;
}
