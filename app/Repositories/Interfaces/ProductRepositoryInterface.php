<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /** 
     * @param array<string, mixed> $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<(int|string), Product> 
     */
    public function getAll(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /** @return Collection<int, Product> */
    public function getByCategory(int $categoryId): Collection;

    public function getById(int $id): ?Product;

    /** @param array<string, mixed> $data */
    public function create(array $data): Product;

    /** @param array<string, mixed> $data */
    public function update(Product $product, array $data): bool;

    public function delete(Product $product): bool;
}
