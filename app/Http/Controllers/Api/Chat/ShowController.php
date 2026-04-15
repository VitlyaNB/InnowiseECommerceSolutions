<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class ShowController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    #[OA\Get(
        path: '/api/chats/{chat}',
        summary: 'Get chat messages',
        description: 'Retrieves a specific chat conversation and its messages.',
        tags: ['Chats'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'chat',
                in: 'path',
                required: true,
                description: 'Chat ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Chat details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Chat not found'),
        ]
    )]
    public function __invoke(Request $request, int $chat): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $chatDto = $this->chatService->getChat($chat, $user->id);
        if (! $chatDto) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        return response()->json($chatDto->toArray());
    }
}
