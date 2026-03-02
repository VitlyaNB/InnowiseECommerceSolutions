<?php


namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Resources\ProductResource;

class GetCategoryProductsAction extends Controller
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function __invoke($categoryId)
    {
        $products = $this->productService->getProductsByCategory($categoryId);

        return ProductResource::collection($products);
    }
}
