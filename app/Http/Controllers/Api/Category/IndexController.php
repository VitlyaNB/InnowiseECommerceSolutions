<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class IndexController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    #[OA\Get(
        path: '/api/categories',
        summary: 'Get list of all categories',
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of categories',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                            new OA\Property(property: 'image_url', type: 'string', nullable: true, example: 'https://cdn.example.com/categories/electronics.jpg'),
                        ]
                    )
                )
            ),
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories();
        return response()->json(CategoryResource::collection($categories));
    }
}
