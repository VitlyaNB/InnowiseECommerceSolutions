<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class GetCategoryProductsAction extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    #[OA\Get(
        path: '/api/categories/{categoryId}/products',
        summary: 'Get products belonging to a specific category',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'categoryId',
                in: 'path',
                required: true,
                description: 'Category ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of products in the category',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ProductResource')
                )
            ),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function __invoke(int $categoryId): AnonymousResourceCollection
    {
        $products = $this->productService->getProductsByCategory($categoryId);
        return ProductResource::collection($products);
    }
}
