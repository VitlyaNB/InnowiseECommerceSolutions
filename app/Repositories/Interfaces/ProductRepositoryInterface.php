<?php

namespace App\Repositories\Interfaces;

use App\DTO\ProductDTO;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function create(ProductDTO $data): Product;

    public function update(int $id, ProductDTO $data): bool;

    public function delete(int $id): bool;
}
