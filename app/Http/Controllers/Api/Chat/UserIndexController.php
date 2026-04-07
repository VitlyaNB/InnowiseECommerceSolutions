<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserIndexController extends Controller
{
    public function __construct(
        private readonly ChatRepositoryInterface $chatRepository
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $chat = $this->chatRepository->findByUserIdWithMessages($user->id);

        if (! $chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        return response()->json($chat->toArray());
    }
}
