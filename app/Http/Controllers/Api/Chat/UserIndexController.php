<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class UserIndexController extends Controller
{
    public function __construct(
        private readonly ChatRepositoryInterface $chatRepository
    ) {}

    #[OA\Get(
        path: '/api/chats',
        summary: 'Get user chats',
        description: 'Retrieves all chat conversations for the authenticated user.',
        tags: ['Chats'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of user chats',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Chat not found'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $chat = $this->chatRepository->findByUserIdWithMessages($user->id);

        if (! $chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        return response()->json($chat->toArray());
    }
}
