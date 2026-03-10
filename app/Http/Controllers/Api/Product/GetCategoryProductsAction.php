<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetCategoryProductsAction extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function __invoke(int $categoryId): AnonymousResourceCollection
    {
        $products = $this->productService->getProductsByCategory($categoryId);
        return ProductResource::collection($products);
    }
}
