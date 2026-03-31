<?php

namespace App\Http\Controllers\Api\Recommendation;

use App\Http\Support\ViewSessionResolver;
use App\Http\Resources\ProductResource;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductController
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
        private readonly ViewSessionResolver $viewSessionResolver,
    ) {}

    public function __invoke(Request $request, int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        $recommendations = $this->recommendationService->getProductRecommendations(
            $id,
            $user?->id,
            $this->viewSessionResolver->resolveSessionId($request)
        );

        return response()->json([
            'also_bought' => ProductResource::collection($recommendations->alsoBought)->resolve(),
            'similar' => ProductResource::collection($recommendations->similar)->resolve(),
            'recently_viewed' => ProductResource::collection($recommendations->recentlyViewed)->resolve(),
        ]);
    }
}
