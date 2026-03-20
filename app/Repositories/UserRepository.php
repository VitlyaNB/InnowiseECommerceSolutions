<?php

namespace App\Repositories;

use App\Dto\RegisterDto;
use App\Dto\UpdateUserDto;
use App\Dto\UserDto;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /** @return array<int, UserDto> */
    public function getAll(): array
    {
        $collection = User::query()
            ->orderBy('created_at', 'desc')
            ->get();

        /** @var array<int, UserDto> $result */
        $result = [];
        foreach ($collection as $user) {
            /** @var User $user */
            $result[] = $this->mapToDto($user);
        }

        return $result;
    }

    public function findByEmail(string $email): ?UserDto
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();

        return $user ? $this->mapToDto($user) : null;
    }

    public function findById(int $id): ?UserDto
    {
        /** @var User|null $user */
        $user = User::query()->find($id);

        return $user ? $this->mapToDto($user) : null;
    }

    public function create(RegisterDto $data): UserDto
    {
        /** @var User $user */
        $user = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'role' => 'user',
            'balance' => 0.0,
        ]);

        return $this->mapToDto($user);
    }

    public function update(int $id, UpdateUserDto $data): bool
    {
        /** @var User|null $user */
        $user = User::query()->find($id);
        if (! $user) {
            return false;
        }

        return $user->update($data->toArray());
    }

    public function delete(int $id): bool
    {
        /** @var User|null $user */
        $user = User::query()->find($id);
        if (! $user) {
            return false;
        }

        return (bool) $user->delete();
    }

    public function verifyCredentials(string $email, string $password): ?UserDto
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();
        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $this->mapToDto($user);
    }

    public function deleteTokens(int $userId): void
    {
        /** @var User|null $user */
        $user = User::query()->find($userId);
        if ($user) {
            $user->tokens()->delete();
        }
    }

    public function topUp(int $userId, float $amount): UserDto
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);
        $user->balance += $amount;
        $user->save();

        return $this->mapToDto($user);
    }

    public function createToken(int $userId, string $tokenName): string
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);

        return $user->createToken($tokenName)->plainTextToken;
    }

    public function findByIdForUpdate(int $id): ?UserDto
    {
        /** @var User|null $user */
        $user = User::query()->lockForUpdate()->find($id);

        return $user ? $this->mapToDto($user) : null;
    }

    public function decrementBalance(int $userId, float $amount): bool
    {
        /** @var User|null $user */
        $user = User::query()->find($userId);

        if (! $user) {
            return false;
        }

        $newBalance = (float) $user->balance - $amount;

        if ($newBalance < 0) {
            return false;
        }

        return $user->update(['balance' => $newBalance]);
    }

    private function mapToDto(User $user): UserDto
    {
        return new UserDto(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            role: $user->role,
            balance: (float) $user->balance,
            emailVerifiedAt: $user->email_verified_at !== null ? $user->email_verified_at->toDateTimeString() : null,
            createdAt: $user->created_at !== null ? $user->created_at->toDateTimeString() : null,
            updatedAt: $user->updated_at !== null ? $user->updated_at->toDateTimeString() : null,
        );
    }
}
