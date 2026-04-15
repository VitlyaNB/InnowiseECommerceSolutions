<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class StartController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    #[OA\Post(
        path: '/api/chats/start',
        summary: 'Start a new chat',
        description: 'Creates a new chat conversation for the authenticated user.',
        tags: ['Chats'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Chat created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $chat = $this->chatService->startChat($user->id);

        return response()->json($chat->toArray());
    }
}
