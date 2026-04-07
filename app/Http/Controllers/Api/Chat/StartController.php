<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StartController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $chat = $this->chatService->startChat($user->id);

        return response()->json($chat->toArray());
    }
}
