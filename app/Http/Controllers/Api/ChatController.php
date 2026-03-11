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

class ChatController extends Controller
{
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
                'user_id' => $user->id,
                'last_message_at' => now(),
            ]);
            $chat->load('messages.user');
        }

        return new ChatResource($chat);
    }

    public function show(Chat $chat): ChatResource
    {
        $this->authorizeAccess($chat);

        $chat->load(['messages.user', 'user']);

        $chat->messages()
            ->where('user_id', '!=', Auth::id())
            ->update(['is_read' => true]);

        return new ChatResource($chat);
    }

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
