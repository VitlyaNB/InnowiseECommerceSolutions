<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Services\ProductService;
use App\DTO\ProductDTO;

class StoreProductAction extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function __invoke(StoreProductRequest $request)
    {
        if ($request->user() && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $dto = ProductDTO::fromRequest($request);
        $product = $this->productService->createProduct($dto);

        return response()->json($product, 201);
    }
}
