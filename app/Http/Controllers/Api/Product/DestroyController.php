<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class DestroyController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    #[OA\Delete(
        path: '/api/products/{id}',
        summary: 'Delete a product (admin only)',
        tags: ['Products'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (admin only)'),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function __invoke(int $id): JsonResponse
    {
        $this->productService->deleteProduct($id);
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
