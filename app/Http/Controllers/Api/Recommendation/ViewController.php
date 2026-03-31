<?php

namespace App\Http\Controllers\Api\Recommendation;

use App\Http\Support\ViewSessionResolver;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ViewController
{
    public function __construct(
        private RecommendationService $recommendationService,
        private ViewSessionResolver $sessionResolver
    ) {}

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $userIdRaw = auth()->id();
        $userId = $userIdRaw !== null ? (int) $userIdRaw : null;
        $this->recommendationService->recordView($userId, $this->sessionResolver->resolveSessionId($request), $id);

        return response()->json(['recorded' => true]);
    }
}
