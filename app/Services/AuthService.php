<?php

namespace App\Services;

use App\Dto\LoginDto;
use App\Dto\LoginResultDto;
use App\Dto\RegisterDto;
use App\Dto\UpdateUserDto;
use App\Dto\UserDto;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Validation\ValidationException;

final readonly class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function register(RegisterDto $data): LoginResultDto
    {
        $userDto = $this->userRepository->create($data);
        $token = $this->userRepository->createToken($userDto->id, 'auth_token');

        return new LoginResultDto(user: $userDto, token: $token);
    }

    /**
     * @return array<int, UserDto>
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->getAll();
    }

    public function login(LoginDto $data): LoginResultDto
    {
        $userDto = $this->userRepository->verifyCredentials($data->email, $data->password);

        if (!$userDto) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        $token = $this->userRepository->createToken($userDto->id, 'auth_token');

        return new LoginResultDto(user: $userDto, token: $token);
    }

    public function updateUser(int $id, UpdateUserDto $dto): bool
    {
        return $this->userRepository->update($id, $dto);
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

    public function logout(int $userId): void
    {
        $this->userRepository->deleteTokens($userId);
    }

    public function topUp(int $userId, float $amount): UserDto
    {
        return $this->userRepository->topUp($userId, $amount);
    }
}
