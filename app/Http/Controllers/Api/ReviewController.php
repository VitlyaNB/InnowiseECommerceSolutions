<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $service
    ) {}

    #[OA\Get(
        path: '/api/products/{id}/reviews',
        summary: 'Get reviews for a product',
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of reviews for the product',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'rating', type: 'integer', example: 5),
                                    new OA\Property(property: 'comment', type: 'string', example: 'Great product!'),
                                    new OA\Property(property: 'likes_count', type: 'integer', example: 3),
                                    new OA\Property(property: 'is_liked', type: 'boolean', example: false),
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function index(int $productId): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getProductReviews($productId)
        ]);
    }

    #[OA\Post(
        path: '/api/reviews',
        summary: 'Create a new review for a product',
        tags: ['Reviews'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id', 'comment'],
                properties: [
                    new OA\Property(property: 'product_id', type: 'integer', example: 1),
                    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
                    new OA\Property(property: 'comment', type: 'string', maxLength: 1000, example: 'Excellent product, highly recommended!'),
                    new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null, description: 'ID of the parent review for reply'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Review created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Отзыв опубликован'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (not purchased, already reviewed)'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'nullable|integer|min:1|max:5',
            'comment'    => 'required|string|max:1000',
            'parent_id'  => 'nullable|exists:reviews,id'
        ]);

        /** @var User $user */
        $user = $request->user();

        try {
            /** @var array<string, mixed> $data */
            $data   = $request->all();
            $review = $this->service->createReview($user->id, $data);
            return response()->json(['message' => 'Отзыв опубликован', 'data' => $review], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    #[OA\Post(
        path: '/api/reviews/{id}/like',
        summary: 'Toggle like on a review',
        tags: ['Reviews'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Review ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Like toggled',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'liked', type: 'boolean', example: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function like(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user    = $request->user();
        $isLiked = $this->service->toggleLike($user->id, $id);
        return response()->json(['liked' => $isLiked]);
    }

    #[OA\Get(
        path: '/api/products/{id}/can-review',
        summary: 'Check if the authenticated user can review a product',
        tags: ['Reviews'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Review permission status',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'can_review', type: 'boolean', example: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function checkPermission(Request $request, int $productId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $can  = $this->service->canReview($user->id, $productId);
        return response()->json(['can_review' => $can]);
    }
}
