<?php

namespace App\Broadcasting;

use App\Models\User;

final class UserChannelв
{
    public function join(User $user, int|string $id): bool
    {
        return (int) $user->id === (int) $id;
    }
}
