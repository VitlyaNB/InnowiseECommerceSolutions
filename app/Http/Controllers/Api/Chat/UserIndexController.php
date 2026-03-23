<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;

final class UserIndexController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function __invoke(): JsonResponse
    {
        $userId = auth()->id();
        $chat = $this->chatService->getUserChat($userId !== false ? (int) $userId : 0);

        if (! $chat) {
            return response()->json(['message' => 'Чат не найден'], 404);
        }

        return response()->json($chat->toArray());
    }
}
