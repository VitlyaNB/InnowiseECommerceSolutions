<?php

namespace App\Broadcasting;

use App\Models\Chat;
use App\Models\User;

final class ChatChannel
{
    public function join(User $user, int|string $chatId): bool
    {
        $chat = Chat::query()->find((int) $chatId);

        if (!$chat) {
            return false;
        }

        return $user->role === 'admin' || (int) $user->id === (int) $chat->user_id;
    }
}
