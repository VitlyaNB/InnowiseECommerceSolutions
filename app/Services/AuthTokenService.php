<?php

namespace App\Services;

use App\Models\User;
use App\Services\Interfaces\AuthTokenServiceInterface;

final readonly class AuthTokenService implements AuthTokenServiceInterface
{
    public function createForUserId(int $userId): string
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);

        return $user->createToken('auth_token')->plainTextToken;
    }
}
