<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class RecommendationController extends Controller
{
    private const VIEW_SESSION_COOKIE = 'view_session';

    public function __construct(
        private readonly RecommendationService $recommendationService
    ) {}

    #[OA\Get(
        path: '/api/recommendations/home',
        summary: 'Get personalized home page recommendations',
        description: 'Returns a personalized list of recommended products based on recently viewed items or random products for new visitors.',
        tags: ['Recommendations'],
        parameters: [
            new OA\Parameter(
                name: 'X-Session-Id',
                in: 'header',
                required: false,
                description: 'Anonymous visitor session ID (used for tracking without login)',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of recommended products',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ProductResource')
                        )
                    ]
                )
            ),
        ]
    )]
    public function home(Request $request): JsonResponse
    {
        $sessionId = $this->resolveViewSessionId($request);

        /** @var User|null $user */
        $user   = $request->user();
        $userId = $user?->id;

        $items = $this->recommendationService->getHomeRecommendations($userId, $sessionId);

        return response()->json([
            'items' => ProductResource::collection($items),
        ]);
    }

    #[OA\Get(
        path: '/api/products/{id}/recommendations',
        summary: 'Get recommendations for a specific product',
        description: 'Returns "also bought", "similar" and "recently viewed" product lists for a given product.',
        tags: ['Recommendations'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'X-Session-Id',
                in: 'header',
                required: false,
                description: 'Anonymous visitor session ID',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product recommendations',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'also_bought', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'similar', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'recently_viewed', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function product(Request $request, int $id): JsonResponse
    {
        $sessionId = $this->resolveViewSessionId($request);

        /** @var User|null $user */
        $user   = $request->user();
        $userId = $user?->id;

        $recommendations = $this->recommendationService->getProductRecommendations($id, $userId, $sessionId);

        return response()->json([
            'also_bought'     => ProductResource::collection($recommendations['also_bought']),
            'similar'         => ProductResource::collection($recommendations['similar']),
            'recently_viewed' => ProductResource::collection($recommendations['recently_viewed']),
        ]);
    }

    #[OA\Post(
        path: '/api/products/{id}/view',
        summary: 'Record a product view for recommendation tracking',
        tags: ['Recommendations'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer', example: 1)
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
    public function view(Request $request, int $id): JsonResponse
    {
        $sessionId = $this->resolveViewSessionId($request);

        /** @var User|null $user */
        $user   = $request->user();
        $userId = $user?->id;

        $this->recommendationService->recordView($userId, $sessionId, $id);

        return response()->json(['recorded' => true]);
    }

    private function resolveViewSessionId(Request $request): string
    {
        $sessionId = $request->cookie(self::VIEW_SESSION_COOKIE);

        if (!is_string($sessionId) || empty($sessionId)) {
            $sessionId = Str::uuid()->toString();
            Cookie::queue(self::VIEW_SESSION_COOKIE, $sessionId, 60 * 24 * 30, '/', null, false, true, false, 'Lax');
        }

        return $sessionId;
    }
}
