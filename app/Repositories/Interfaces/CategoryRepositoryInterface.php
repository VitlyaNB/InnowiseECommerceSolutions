<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Category;

    /**
     * Теперь принимает массив вместо CategoryDTO для гибкости
     */
    public function create(array $data): Category;

    /**
     * Теперь принимает массив вместо CategoryDTO
     */
    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
