<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['category', 'images'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getById(int $id): Product
    {
        return Product::with(['category', 'images'])->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product;
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return Product::with(['category', 'images'])
            ->where('category_id', $categoryId)
            ->orderByDesc('created_at')
            ->get();
    }
}
