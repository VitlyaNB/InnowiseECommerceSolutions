<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use OpenApi\Attributes as OA;

class UpdateController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    #[OA\Post(
        path: '/api/products/{id}',
        summary: 'Update an existing product (admin only)',
        description: 'Uses POST with multipart/form-data to support image uploads. All fields are optional.',
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Updated Smartphone'),
                        new OA\Property(property: 'description', type: 'string', example: 'An even better phone'),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 899.99),
                        new OA\Property(property: 'category_id', type: 'integer', example: 1),
                        new OA\Property(property: 'quantity', type: 'integer', example: 5),
                        new OA\Property(
                            property: 'images[]',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                            description: 'New product image files'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Product updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (admin only)'),
            new OA\Response(response: 404, description: 'Product not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function __invoke(UpdateProductRequest $request, int $id): ProductResource
    {
        $product = $this->productService->updateProduct($id, $request->toDto());

        return new ProductResource($product);
    }
}
