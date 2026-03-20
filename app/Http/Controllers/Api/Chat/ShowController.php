<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShowController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function __invoke(Request $request, int $chat): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $chatDto = $this->chatService->getChat($chat, $user->id);
        if (! $chatDto) {
            return response()->json(['message' => 'Чат не найден'], 404);
        }

        return response()->json($chatDto->toArray());
    }
}
