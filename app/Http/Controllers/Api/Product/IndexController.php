<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class IndexController extends Controller
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
    public function __invoke(ProductIndexRequest $request): JsonResponse
    {
        $products = $this->productService->getAllProducts($request->toDto());

        return response()->json([
            'data' => array_map(static fn ($productDto) => $productDto->toArray(), $products->items),
            'meta' => [
                'total' => $products->total,
                'per_page' => $products->perPage,
                'current_page' => $products->currentPage,
                'last_page' => $products->lastPage,
            ],
        ]);
    }
}
