<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $chat = $this->chatRepository->findByUserIdWithMessages($user->id);

        if (! $chat) {
            return response()->json(['message' => 'Чат не найден'], 404);
        }

        return response()->json($chat->toArray());
    }
}
