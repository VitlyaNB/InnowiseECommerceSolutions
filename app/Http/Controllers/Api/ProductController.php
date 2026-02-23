<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Services\ProductService;
use App\DTO\ProductDTO;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(): JsonResponse
    {
        $products = $this->productService->getCatalog();
        return response()->json($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $dto = ProductDTO::fromRequest($request);

        $product = $this->productService->createProduct($dto);

        return response()->json($product, 201);
    }
}
