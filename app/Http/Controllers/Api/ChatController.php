<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class ChatController extends Controller
{
    #[OA\Get(
        path: '/api/chats',
        summary: 'Get chats (all chats for admin, own chat for user)',
        description: 'Admin users receive a list of all chats ordered by last message. Regular users receive their own chat (created automatically if it does not exist).',
        tags: ['Chat'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Chat(s) data',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(
                            description: 'Admin: array of chats',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        ),
                        new OA\Schema(
                            description: 'User: single chat object',
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection|ChatResource
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->role === 'admin') {
            $chats = Chat::with('user', 'messages')->orderBy('last_message_at', 'desc')->get();
            return ChatResource::collection($chats);
        }

        $chat = Chat::with('messages.user')
            ->where('user_id', $user->id)
            ->first();

        if (!$chat) {
            /** @var Chat $chat */
            $chat = Chat::query()->create([
                'user_id'         => $user->id,
                'last_message_at' => now(),
            ]);
            $chat->load('messages.user');
        }

        return new ChatResource($chat);
    }

    #[OA\Get(
        path: '/api/chats/{chat}',
        summary: 'Get a single chat with messages',
        description: 'Marks all messages from the other party as read.',
        tags: ['Chat'],
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
            new OA\Response(response: 200, description: 'Chat details with messages'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (not your chat)'),
            new OA\Response(response: 404, description: 'Chat not found'),
        ]
    )]
    public function show(Chat $chat): ChatResource
    {
        $this->authorizeAccess($chat);

        $chat->load(['messages.user', 'user']);

        $chat->messages()
            ->where('user_id', '!=', Auth::id())
            ->update(['is_read' => true]);

        return new ChatResource($chat);
    }

    #[OA\Post(
        path: '/api/chats/{chat}/messages',
        summary: 'Send a message in a chat',
        description: 'Stores the message and broadcasts it via Pusher to other participants.',
        tags: ['Chat'],
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
                    new OA\Property(property: 'message', type: 'string', example: 'Hello, I have a question about my order.'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Message sent'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (not your chat)'),
            new OA\Response(response: 404, description: 'Chat not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request, Chat $chat): MessageResource
    {
        $this->authorizeAccess($chat);

        $request->validate([
            'message' => 'required|string',
        ]);

        /** @var string $messageContent */
        $messageContent = $request->input('message');

        /** @var Message $message */
        $message = Message::query()->create([
            'chat_id' => $chat->id,
            'user_id' => (int) Auth::id(),
            'message' => $messageContent,
        ]);

        $chat->update(['last_message_at' => now()]);

        broadcast(new MessageSent($message->load('user')))->toOthers();

        return new MessageResource($message);
    }

    #[OA\Post(
        path: '/api/chats/start',
        summary: 'Start (or retrieve) the authenticated user\'s chat',
        description: 'If a chat for the user already exists, it is returned; otherwise a new one is created.',
        tags: ['Chat'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Chat data'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function startChat(Request $request): ChatResource
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Chat $chat */
        $chat = Chat::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['last_message_at' => now()]
        );

        return new ChatResource($chat);
    }

    private function authorizeAccess(Chat $chat): void
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->id !== $chat->user_id) {
            abort(403, 'Доступ запрещен');
        }
    }
}
