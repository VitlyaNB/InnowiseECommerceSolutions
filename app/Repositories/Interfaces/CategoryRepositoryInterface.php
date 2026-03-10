<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    /** @return Collection<int, Category> */
    public function getAll(): Collection;

    public function findById(int $id): ?Category;

    /** @param array<string, mixed> $data */
    public function create(array $data): Category;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Category;

    public function delete(int $id): bool;
}
