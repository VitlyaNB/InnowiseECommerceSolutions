<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function getAllActive()
    {
        return Product::where('is_active', true)->get();
    }
}
