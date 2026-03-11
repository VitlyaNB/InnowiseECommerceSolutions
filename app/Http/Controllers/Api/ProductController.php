<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\DTO\ProductDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    #[OA\Get(
        path: '/api/products',
        summary: 'Get paginated list of products',
        description: 'Supports filtering by category, price range, and stock availability.',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Number of products per page', schema: new OA\Schema(type: 'integer', default: 15, example: 15)),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, description: 'Filter by category ID', schema: new OA\Schema(type: 'integer', example: 2)),
            new OA\Parameter(name: 'price_min', in: 'query', required: false, description: 'Minimum price', schema: new OA\Schema(type: 'number', format: 'float', example: 10.0)),
            new OA\Parameter(name: 'price_max', in: 'query', required: false, description: 'Maximum price', schema: new OA\Schema(type: 'number', format: 'float', example: 500.0)),
            new OA\Parameter(name: 'in_stock', in: 'query', required: false, description: 'Filter to in-stock items only', schema: new OA\Schema(type: 'boolean', example: true)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated product list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductResource')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $filters = $request->only(['category_id', 'price_min', 'price_max', 'in_stock']);

        $products = $this->productService->getAllProducts($filters, $perPage);

        return ProductResource::collection($products)->response();
    }

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
    public function store(StoreProductRequest $request): JsonResponse
    {
        $dto     = ProductDTO::fromRequest($request);
        $product = $this->productService->createProduct($dto);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

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
    public function update(UpdateProductRequest $request, int $id): ProductResource
    {
        $dto     = ProductDTO::fromRequest($request);
        $product = $this->productService->updateProduct($id, $dto);

        return new ProductResource($product);
    }

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
    public function destroy(int $id): JsonResponse
    {
        $this->productService->deleteProduct($id);
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
