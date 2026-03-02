<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchProductAction extends Controller
{
    public function __invoke(Request $request)
    {
        $query = $request->input('q');

        $products = Product::search($query)
            ->where('is_active', true)
            ->paginate(12);

        $products->load(['category', 'images']);

        return ProductResource::collection($products);
    }
}
