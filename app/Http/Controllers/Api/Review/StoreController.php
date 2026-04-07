<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Throwable;

final class StoreController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function __invoke(StoreReviewRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $review = $this->reviewService->createReview($user->id, $request->toDto());

            return response()->json([
                'message' => 'Review published',
                'data' => $review->toArray(),
            ], 201);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 403);
        }
    }
}
