<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;

class GetProductByIdAction extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function __invoke(int $id)
    {
        $product = $this->productService->getProductById($id);

        return response()->json((new ProductResource($product))->resolve());
    }
}
