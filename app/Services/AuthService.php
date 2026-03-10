<?php

namespace App\Services;

use App\DTO\RegisterDTO;
use App\DTO\LoginDTO;
use App\DTO\UpdateUserDTO;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * @return array{user: User, token: string}
     */
    public function register(RegisterDTO $data): array
    {
        $user = $this->userRepository->create($data);
        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getAllUsers()
    {
        return $this->userRepository->getAll();
    }

    /**
     * @return array{user: User, token: string}
     */
    public function login(LoginDTO $data): array
    {
        $user = $this->userRepository->findByEmail($data->email);

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function updateUser(int $id, UpdateUserDTO $dto): bool
    {
        return $this->userRepository->update($id, $dto->toArray());
    }

    public function deleteUser(int $userIdToDelete, int $currentUserId): bool
    {
        if ($userIdToDelete === $currentUserId) {
            throw ValidationException::withMessages([
                'user' => ['Нельзя удалить самого себя']
            ]);
        }

        return $this->userRepository->delete($userIdToDelete);
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
