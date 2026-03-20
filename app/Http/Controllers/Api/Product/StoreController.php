<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class StoreController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    #[OA\Post(
        path: '/api/products',
        summary: 'Create a new product (admin only)',
        tags: ['Products'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'price', 'category_id', 'quantity'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'New Smartphone'),
                        new OA\Property(property: 'description', type: 'string', example: 'A great phone'),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 999.99),
                        new OA\Property(property: 'category_id', type: 'integer', example: 1),
                        new OA\Property(property: 'quantity', type: 'integer', example: 10),
                        new OA\Property(
                            property: 'images[]',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                            description: 'Product image files'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Product created successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (admin only)'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function __invoke(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->toDto());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }
}
