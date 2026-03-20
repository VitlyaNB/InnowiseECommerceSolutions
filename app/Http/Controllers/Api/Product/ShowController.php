<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class ShowController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Get a single product by ID',
        description: 'Retrieves detailed product information including images.',
        tags: ['Products'],
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
            new OA\Response(
                response: 200,
                description: 'Product details',
                content: new OA\JsonContent(ref: '#/components/schemas/ProductResource')
            ),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function __invoke(int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);

        if (! $product) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(200);
    }
}
