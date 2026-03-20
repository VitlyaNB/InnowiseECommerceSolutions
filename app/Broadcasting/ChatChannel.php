<?php

namespace App\Broadcasting;

use App\Models\User;
use App\Repositories\Interfaces\ChatRepositoryInterface;

final class ChatChannel
{
    public function __construct(
        private readonly ChatRepositoryInterface $chatRepository
    ) {}

    public function join(User $user, int|string $chatId): bool
    {
        $ownerId = $this->chatRepository->getChatOwnerId((int) $chatId);

        if ($ownerId === null) {
            return false;
        }

        return $user->role === 'admin' || (int) $user->id === $ownerId;
    }
}
