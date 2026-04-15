<?php

namespace App\Http\Controllers\Api\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class StoreMessageController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    #[OA\Post(
        path: '/api/chats/{chat}/messages',
        summary: 'Send a chat message',
        description: 'Sends a message to an existing chat conversation.',
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['message'],
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Hello, I need help'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Message sent',
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
    public function __invoke(StoreChatMessageRequest $request, int $chat): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var array<string, string> $data */
        $data = $request->validated();

        $message = $this->chatService->sendMessage($chat, $user->id, $data['message']);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message->toArray(), 201);
    }
}
