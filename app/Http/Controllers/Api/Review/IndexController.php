<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\User;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class IndexController extends Controller
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository
    ) {}

    #[OA\Get(
        path: '/api/products/{productId}/reviews',
        summary: 'Get product reviews',
        description: 'Retrieves all reviews for a specific product, including like status for authenticated users.',
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
                description: 'List of product reviews',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ReviewResource')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
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
