<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserIndexController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $chat = $this->chatService->getUserChat($user->id);

        if (! $chat) {
            return response()->json(['message' => 'Чат не найден'], 404);
        }

        return response()->json($chat->toArray());
    }
}
