<?php

namespace App\Repositories;
use App\Models\Product;
use App\DTO\ProductDTO;

class ProductRepository
{
    public function create(ProductDTO $data): Product
    {
        return Product::create([
            'name' => $data->name,
            'price' => $data->price,
            'category_id' => $data->category_id,
            'description' => $data->description,
            'quantity' => 0, // Значение по умолчанию или из DTO
            'is_active' => true,
        ]);
    }
    public function getAllActive()
    {
        return Product::where('is_active', true)->get();
    }
}
