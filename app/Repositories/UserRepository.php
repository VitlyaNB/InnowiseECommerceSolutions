<?php

namespace App\Repositories;

use App\Models\User;
use App\DTO\RegisterDTO;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    /** @return Collection<int, User> */
    public function getAll(): Collection
    {
        /** @var Collection<int, User> $users */
        $users = User::query()->orderBy('created_at', 'desc')->get();
        return $users;
    }

    public function findByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();
        return $user;
    }

    public function create(RegisterDTO $data): User
    {
        return User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
        ]);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        /** @var User $user */
        $user = User::findOrFail($id);
        return $user->update($data);
    }

    public function delete(int $id): bool
    {
        /** @var User $user */
        $user = User::findOrFail($id);
        return (bool) $user->delete();
    }
}
