<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CanReviewController extends Controller
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function __invoke(Request $request, int $productId): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $userId = $user->id;
        $canReview = $this->reviewRepository->canReview($userId, $productId);

        return response()->json(['can_review' => $canReview]);
    }
}
