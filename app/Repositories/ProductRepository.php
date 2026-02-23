<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAllActive(): Collection
    {
        return Product::where('is_active', true)->get();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }
}
