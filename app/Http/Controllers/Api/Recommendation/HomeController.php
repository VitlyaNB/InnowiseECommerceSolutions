<?php

namespace App\Http\Controllers\Api\Recommendation;

use App\Http\Resolvers\ViewSessionResolver;
use App\Http\Resources\ProductResource;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class HomeController
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
        private readonly ViewSessionResolver $viewSessionResolver,
    ) {}

    #[OA\Get(
        path: '/api/recommendations/home',
        summary: 'Get home page recommendations',
        description: 'Retrieves personalized product recommendations for the home page based on user history and view session.',
        tags: ['Recommendations'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recommended products',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductResource')),
                    ]
                )
            ),
        ]
    )]
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
