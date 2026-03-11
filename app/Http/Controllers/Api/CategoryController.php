<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use App\DTO\CategoryDTO;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
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
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories();

        return response()->json(CategoryResource::collection($categories));
    }

    #[OA\Post(
        path: '/api/categories',
        summary: 'Create a new category',
        tags: ['Categories'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'Category image file'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Category created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (admin only)'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $dto      = CategoryDTO::fromRequest($request);
        $category = $this->categoryService->createCategory($dto);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Post(
        path: '/api/categories/{id}',
        summary: 'Update an existing category',
        description: 'Uses POST with multipart/form-data to support file upload (category image replacement).',
        tags: ['Categories'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Category ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Updated Electronics'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'New category image file'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Category updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (admin only)'),
            new OA\Response(response: 404, description: 'Category not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(StoreCategoryRequest $request, int $id): JsonResponse
    {
        $dto      = CategoryDTO::fromRequest($request);
        $category = $this->categoryService->updateCategory($id, $dto);

        return response()->json(new CategoryResource($category));
    }

    #[OA\Delete(
        path: '/api/categories/{id}',
        summary: 'Delete a category',
        tags: ['Categories'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Category ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (admin only)'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $this->categoryService->deleteCategory($id);

        return response()->json(['message' => 'Category deleted']);
    }
}
