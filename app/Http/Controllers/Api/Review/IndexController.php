<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\User;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class IndexController extends Controller
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository
    ) {}

    public function __invoke(Request $request, int $productId): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        $reviews = $this->reviewRepository->getProductReviews($productId, $user?->id);

        return response()->json([
            'data' => array_map(
                static fn ($reviewDto) => (new ReviewResource($reviewDto))->toArray($request),
                $reviews
            ),
        ]);
    }
}
