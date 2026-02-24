<?php


namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Services\ProductService;

class GetProductByIdAction extends Controller
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function __invoke($id)
    {
        return response()->json($this->productService->getProductById($id));
    }
}
