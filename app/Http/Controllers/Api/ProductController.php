<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Services\ProductService;
use App\DTO\ProductDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->productService->getAllProducts());
    }

    public function update(StoreProductRequest $request, int $id): JsonResponse
    {
        // Проверка прав, пока тут оставлю йопта

        if ($request->user() && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $dto = ProductDTO::fromRequest($request);
        $product = $this->productService->updateProduct($id, $dto);

        return response()->json($product);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->user() && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $this->productService->deleteProduct($id);

        return response()->json(['message' => 'Товар успешно удален']);
    }
}
