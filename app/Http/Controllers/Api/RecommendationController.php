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

class RecommendationController extends Controller
{
    private const VIEW_SESSION_COOKIE = 'view_session';

    public function __construct(
        private readonly RecommendationService $recommendationService
    ) {}

    public function view(Request $request, int $id): JsonResponse
    {
        $sessionId = $this->resolveViewSessionId($request);
        
        /** @var User|null $user */
        $user = $request->user();
        $userId = $user?->id;

        $this->recommendationService->recordView($userId, $sessionId, $id);

        return response()->json(['ok' => true]);
    }

    public function product(Request $request, int $id): JsonResponse
    {
        $sessionId = $this->resolveViewSessionId($request);
        
        /** @var User|null $user */
        $user = $request->user();
        $userId = $user?->id;

        $data = $this->recommendationService->getProductRecommendations($id, $userId, $sessionId);

        return response()->json([
            'also_bought' => ProductResource::collection($data['also_bought']),
            'similar' => ProductResource::collection($data['similar']),
            'recently_viewed' => ProductResource::collection($data['recently_viewed']),
        ]);
    }

    public function home(Request $request): JsonResponse
    {
        $sessionId = $this->resolveViewSessionId($request);
        
        /** @var User|null $user */
        $user = $request->user();
        $userId = $user?->id;

        $items = $this->recommendationService->getHomeRecommendations($userId, $sessionId);

        return response()->json([
            'items' => ProductResource::collection($items),
        ]);
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
