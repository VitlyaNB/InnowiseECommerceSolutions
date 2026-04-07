<?php

namespace App\Http\Controllers\Api\Recommendation;

use App\Http\Resolvers\ViewSessionResolver;
use App\Http\Resources\ProductResource;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class HomeController
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
        private readonly ViewSessionResolver $viewSessionResolver,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        $items = $this->recommendationService->getHomeRecommendations($user?->id, $this->viewSessionResolver->resolveSessionId($request));

        return response()->json([
            'items' => ProductResource::collection($items)->resolve(),
        ]);
    }
}
