<?php

namespace App\Repositories;

use App\DTO\ProductDTO;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return Product::query()
            ->with('category')
            ->find($id);
    }


    public function create(ProductDTO $data): Product
    {
        return Product::create($data->toArray());
    }


    public function update(int $id, ProductDTO $data): bool
    {
        $product = Product::query()->findOrFail($id);

        return $product->update(array_filter($data->toArray(), fn($value) => $value !== null));
    }


    public function delete(int $id): bool
    {
        $product = Product::query()->find($id);

        if (!$product) {
            return false;
        }

        return (bool) $product->delete();
    }
}
