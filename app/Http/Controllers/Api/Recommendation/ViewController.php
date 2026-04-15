<?php

namespace App\Http\Controllers\Api\Recommendation;

use App\Http\Resolvers\ViewSessionResolver;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class ViewController
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
        private readonly ViewSessionResolver $sessionResolver
    ) {}

    #[OA\Post(
        path: '/api/products/{id}/view',
        summary: 'Record product view',
        description: 'Records a product view event for recommendation analytics.',
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
                description: 'View recorded',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'recorded', type: 'boolean', example: true),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $userId = $user instanceof User ? $user->id : null;
        $this->recommendationService->recordView($userId, $this->sessionResolver->resolveSessionId($request), $id);

        return response()->json(['recorded' => true]);
    }
}
