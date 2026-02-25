<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(int $perPage = 15)
    {
        return Product::query()
            ->with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByCategory(int $categoryId)
    {
        return Product::with(['category', 'images'])
            ->where('category_id', $categoryId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getById(int $id)
    {
        return Product::with(['category', 'images'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data)
    {
        $product->update($data);
        return $product;
    }

    public function delete(Product $product)
    {
        return $product->delete();
    }
}
