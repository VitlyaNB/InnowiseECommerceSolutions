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
        $query = $request->input('query') ?? $request->input('q');

        if (!$query) {
            return ProductResource::collection([]);
        }

        $products = Product::search($query)
            ->query(function ($builder) {
                $builder->with(['category', 'images'])
                    ->where('is_active', true);
            })
            ->paginate(12);

        return ProductResource::collection($products);
    }
}
