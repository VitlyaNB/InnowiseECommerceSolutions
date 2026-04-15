<?php

namespace App\Http\Controllers\Api\Recommendation;

use App\Http\Resolvers\ViewSessionResolver;
use App\Http\Resources\ProductResource;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class ProductController
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
        private readonly ViewSessionResolver $viewSessionResolver,
    ) {}

    #[OA\Get(
        path: '/api/products/{id}/recommendations',
        summary: 'Get product recommendations',
        description: 'Retrieves personalized recommendations for a product based on purchase history, similar products, and viewing history.',
        tags: ['Recommendations'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recommendations',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'also_bought', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductResource')),
                        new OA\Property(property: 'similar', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductResource')),
                        new OA\Property(property: 'recently_viewed', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductResource')),
                    ]
                )
            ),
        ]
    )]
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
