<?php


namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Services\ProductService;

class GetCategoryProductsAction extends Controller
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function __invoke($categoryId)
    {
        return response()->json($this->productService->getProductsByCategory($categoryId));
    }
}
