<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductSearchRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SearchController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    #[OA\Get(
        path: '/api/products/search',
        summary: 'Search products using Elasticsearch',
        description: 'Full-text search with filters, sorting and aggregation. Returns paginated results plus filter metadata (available categories, min/max prices).',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'query', in: 'query', required: false, description: 'Search term', schema: new OA\Schema(type: 'string', example: 'smartphone')),
            new OA\Parameter(name: 'categories', in: 'query', required: false, description: 'Array of category IDs to filter by', schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer'))),
            new OA\Parameter(name: 'min_price', in: 'query', required: false, description: 'Minimum price filter', schema: new OA\Schema(type: 'number', format: 'float', example: 10.0)),
            new OA\Parameter(name: 'max_price', in: 'query', required: false, description: 'Maximum price filter', schema: new OA\Schema(type: 'number', format: 'float', example: 999.99)),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort order',
                schema: new OA\Schema(type: 'string', enum: ['created_at_desc', 'price_asc', 'price_desc', 'name_asc'], default: 'created_at_desc')
            ),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Results per page', schema: new OA\Schema(type: 'integer', default: 12)),
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Search results',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductResource')),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer', example: 42),
                                new OA\Property(property: 'per_page', type: 'integer', example: 12),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'number', example: 4),
                            ]
                        ),
                        new OA\Property(
                            property: 'filters',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'categories', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'min_price', type: 'number', example: 5.0),
                                new OA\Property(property: 'max_price', type: 'number', example: 1999.0),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(ProductSearchRequest $request): JsonResponse
    {
        $resultDto = $this->productService->search($request->toDto());

        return response()->json([
            'data' => array_map(static fn ($productDto) => $productDto->toArray(), $resultDto->data),
            'meta' => $resultDto->meta,
            'filters' => $resultDto->filters,
        ]);
    }
}
