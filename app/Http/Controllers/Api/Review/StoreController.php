<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Throwable;

final class StoreController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    #[OA\Post(
        path: '/api/reviews',
        summary: 'Create a product review',
        description: 'Creates a new review for a product. User must have purchased the product to leave a review.',
        tags: ['Reviews'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id', 'rating', 'comment'],
                properties: [
                    new OA\Property(property: 'product_id', type: 'integer', example: 5),
                    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
                    new OA\Property(property: 'comment', type: 'string', example: 'Great product!'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Review created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Review published'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'User cannot review this product'),
        ]
    )]
    public function __invoke(StoreReviewRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $review = $this->reviewService->createReview($user->id, $request->toDto());

            return response()->json([
                'message' => 'Review published',
                'data' => (new ReviewResource($review))->toArray($request),
            ], 201);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 403);
        }
    }
}
