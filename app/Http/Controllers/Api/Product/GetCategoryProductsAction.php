<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;

class GetCategoryProductsAction extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function __invoke($categoryId)
    {
        $products = $this->productService->getProductsByCategory($categoryId);
        return ProductResource::collection($products);
    }
}
