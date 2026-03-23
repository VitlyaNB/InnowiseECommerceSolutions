<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Repositories\ReviewRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CanReviewController extends Controller
{
    public function __construct(
        private ReviewRepository $reviewRepository
    ) {}

    public function __invoke(Request $request, int $productId): JsonResponse
    {
        $userId = $request->user()->id;
        $canReview = $this->reviewRepository->canReview($userId, $productId);

        return response()->json(['can_review' => $canReview]);
    }
}
