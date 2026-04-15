<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class CanReviewController extends Controller
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    #[OA\Get(
        path: '/api/products/{productId}/can-review',
        summary: 'Check if user can review a product',
        description: 'Determines whether the authenticated user can leave a review for a specific product.',
        tags: ['Reviews'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'productId',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Check result',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'can_review', type: 'boolean', example: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function __invoke(Request $request, int $productId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $userId = $user->id;
        $canReview = $this->reviewRepository->canReview($userId, $productId);

        return response()->json(['can_review' => $canReview]);
    }
}
