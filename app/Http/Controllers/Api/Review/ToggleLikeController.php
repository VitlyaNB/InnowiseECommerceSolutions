<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class ToggleLikeController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    #[OA\Post(
        path: '/api/reviews/{id}/like',
        summary: 'Toggle review like',
        description: 'Likes or unlikes a review for the authenticated user.',
        tags: ['Reviews'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Review ID',
                schema: new OA\Schema(type: 'integer', example: 3)
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
    public function __invoke(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $isLiked = $this->reviewService->toggleLike($user->id, $id);

        return response()->json(['liked' => $isLiked]);
    }
}
