<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\DTO\ProductDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    /**
     * Получить список всех товаров.
     */
    public function index(): AnonymousResourceCollection
    {
        $products = $this->productService->getAllProducts();

        return ProductResource::collection($products);
    }

    /**
     * Создать новый товар.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $dto = ProductDTO::fromRequest($request);
        $product = $this->productService->createProduct($dto);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Получить информацию о конкретном товаре.
     */
    public function show(int $id): ProductResource
    {
        $product = $this->productService->getProductById($id);

        return new ProductResource($product);
    }

    /**
     * Обновить товар.
     */
    public function update(StoreProductRequest $request, int $id): ProductResource
    {
        $dto = ProductDTO::fromRequest($request);
        $product = $this->productService->updateProduct($id, $dto);

        return new ProductResource($product);
    }

    /**
     * Удалить товар.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->productService->deleteProduct($id);

        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
