<?php

namespace App\Http\Controllers\Api\Recommendation;

use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

final class HomeController
{
    private const VIEW_SESSION_COOKIE = 'view_session';

    public function __construct(
        private RecommendationService $recommendationService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        $items = $this->recommendationService->getHomeRecommendations($user?->id, $this->resolveViewSessionId($request));

        return response()->json([
            'items' => array_map(static fn ($productDto) => $productDto->toArray(), $items),
        ]);
    }

    private function resolveViewSessionId(Request $request): string
    {
        $sessionId = $request->cookie(self::VIEW_SESSION_COOKIE);

        if (!is_string($sessionId) || $sessionId === '') {
            $sessionId = Str::uuid()->toString();
            Cookie::queue(self::VIEW_SESSION_COOKIE, $sessionId, 60 * 24 * 30, '/', null, false, true, false, 'Lax');
        }

        return $sessionId;
    }
}
