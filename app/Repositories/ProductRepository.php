<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(int $perPage = 15)
    {
        return Product::query()
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByCategory(int $categoryId)
    {
        return Product::with('category')
            ->where('category_id', $categoryId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getById(int $id)
    {
        return Product::with('category')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Product::create($data);
    }
}
