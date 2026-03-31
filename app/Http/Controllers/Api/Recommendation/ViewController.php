<?php

namespace App\Http\Controllers\Api\Recommendation;

use App\Http\Support\ViewSessionResolver;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ViewController
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
        private readonly ViewSessionResolver $sessionResolver
    ) {}

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $userId = $user instanceof User ? $user->id : null;
        $this->recommendationService->recordView($userId, $this->sessionResolver->resolveSessionId($request), $id);

        return response()->json(['recorded' => true]);
    }
}
