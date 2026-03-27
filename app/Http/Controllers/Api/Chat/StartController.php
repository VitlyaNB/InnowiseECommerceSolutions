<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;

final class StartController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function __invoke(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Требуется авторизация'], 401);
        }

        $chat = $this->chatService->startChat($user->id);

        return response()->json($chat->toArray());
    }
}
