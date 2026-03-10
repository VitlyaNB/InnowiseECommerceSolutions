<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class GetProductByIdAction extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function __invoke(int $id): ProductResource|JsonResponse
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }

        return new ProductResource($product->load('images'));
    }
}
