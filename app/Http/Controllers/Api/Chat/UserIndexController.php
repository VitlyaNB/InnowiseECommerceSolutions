<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use Illuminate\Http\JsonResponse;

final class UserIndexController extends Controller
{
    public function __construct(
        private readonly ChatRepositoryInterface $chatRepository
    ) {}

    public function __invoke(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Требуется авторизация'], 401);
        }

        $chat = $this->chatRepository->findByUserIdWithMessages($user->id);

        if (! $chat) {
            return response()->json(['message' => 'Чат не найден'], 404);
        }

        return response()->json($chat->toArray());
    }
}
