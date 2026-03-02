<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\DTO\ProductDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index()
    {
        return ProductResource::collection($this->productService->getAllProducts()->load(['images', 'category']));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $dto = ProductDTO::fromRequest($request);
        $product = $this->productService->createProduct($dto);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function update(StoreProductRequest $request, int $id): ProductResource
    {
        $dto = ProductDTO::fromRequest($request);
        $product = $this->productService->updateProduct($id, $dto);

        return new ProductResource($product);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->productService->deleteProduct($id);
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
