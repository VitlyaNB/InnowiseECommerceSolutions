<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;

final class AdminIndexController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function __invoke(): JsonResponse
    {
        $chats = $this->chatService->getAdminChats();

        return response()->json(array_map(static fn ($chatDto) => $chatDto->toArray(), $chats));
    }
}
