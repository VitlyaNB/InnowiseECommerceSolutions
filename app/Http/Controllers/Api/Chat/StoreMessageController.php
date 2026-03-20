<?php

namespace App\Http\Controllers\Api\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;

final class StoreMessageController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

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
