<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ToggleLikeController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function __invoke(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $isLiked = $this->reviewService->toggleLike($user->id, $id);

        return response()->json(['liked' => $isLiked]);
    }
}
