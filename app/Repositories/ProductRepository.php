<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    /** 
     * @param array<string, mixed> $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<(int|string), Product> 
     */
    public function getAll(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query()->with(['images', 'category']);

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<(int|string), Product> $paginator */
        $paginator = $query->latest()->paginate($perPage);
        return $paginator;
    }

    /** @return Collection<int, Product> */
    public function getByCategory(int $categoryId): Collection
    {
        /** @var Collection<int, Product> $products */
        $products = Product::query()
            ->with(['images', 'category'])
            ->where('category_id', $categoryId)
            ->get();
        return $products;
    }

    public function getById(int $id): ?Product
    {
        /** @var Product|null $product */
        $product = Product::query()->with(['images', 'category'])->find($id);
        return $product;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }
}
