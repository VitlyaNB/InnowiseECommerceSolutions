<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;

final class StartController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function __invoke(): JsonResponse
    {
        $userId = auth()->id();
        $chat = $this->chatService->startChat($userId !== false ? (int) $userId : 0);

        return response()->json($chat->toArray());
    }
}
