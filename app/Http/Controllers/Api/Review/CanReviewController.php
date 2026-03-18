<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CanReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function __invoke(Request $request, int $productId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $canReview = $this->reviewService->canReview($user->id, $productId);

        return response()->json(['can_review' => $canReview]);
    }
}
