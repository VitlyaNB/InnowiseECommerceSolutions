<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use App\DTO\CategoryDTO;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories();

        return response()->json(CategoryResource::collection($categories)->resolve());
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $dto = CategoryDTO::fromRequest($request);
        $category = $this->categoryService->createCategory($dto);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function update(StoreCategoryRequest $request, int $id): JsonResponse
    {
        $dto = CategoryDTO::fromRequest($request);
        $category = $this->categoryService->updateCategory($id, $dto);

        return response()->json(new CategoryResource($category));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->categoryService->deleteCategory($id);

        return response()->json(['message' => 'Category deleted']);
    }
}
