<?php

namespace App\Repositories\Interfaces;

use App\Dto\CategoryDto;

interface CategoryRepositoryInterface
{
    /** @return array<int, CategoryDto> */
    public function getAll(): array;

    public function findById(int $id): ?CategoryDto;

    public function create(CategoryDto $data): CategoryDto;

    public function update(int $id, CategoryDto $data): bool;

    public function delete(int $id): bool;

    public function existsByName(string $name): bool;
}
