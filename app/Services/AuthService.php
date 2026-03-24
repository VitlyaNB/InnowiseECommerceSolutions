<?php

namespace App\Services;

use App\Dto\AuthDto;
use App\Dto\LoginDto;
use App\Dto\RegisterDto;
use App\Dto\UpdateUserDto;
use App\Dto\UserDto;
use App\Models\User;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CartItemRepositoryInterface $cartRepository,
    ) {}

    public function register(RegisterDto $dto): AuthDto
    {
        $userDto = $this->userRepository->create($dto);
        $user = User::query()->findOrFail($userDto->id);
        $token = $user->createToken('auth_token')->plainTextToken;

        return new AuthDto(user: $userDto, token: $token);
    }

    /**
     * @return array<int, UserDto>
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->getAll();
    }

    public function login(LoginDto $dto, ?string $sessionId = null): AuthDto
    {
        $userDto = $this->verifyCredentials($dto->email, $dto->password);

        if (! $userDto) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        if ($sessionId !== null) {
            $this->mergeSessionToUser($sessionId, $userDto->id);
        }

        $user = User::query()->findOrFail($userDto->id);
        $token = $user->createToken('auth_token')->plainTextToken;

        return new AuthDto(user: $userDto, token: $token);
    }

    public function verifyCredentials(string $email, string $password): ?UserDto
    {
        $userDto = $this->userRepository->findByEmailWithPassword($email);

        if (! $userDto) {
            return null;
        }

        if (! Hash::check($password, $userDto->passwordHash ?? '')) {
            return null;
        }

        $userDtoWithoutPassword = new UserDto(
            id: $userDto->id,
            name: $userDto->name,
            email: $userDto->email,
            role: $userDto->role,
            balance: $userDto->balance,
            emailVerifiedAt: $userDto->emailVerifiedAt,
            createdAt: $userDto->createdAt,
            updatedAt: $userDto->updatedAt,
        );

        return $userDtoWithoutPassword;
    }

    private function mergeSessionToUser(string $sessionId, int $userId): void
    {
        $this->cartRepository->mergeSessionToUser($sessionId, $userId);
    }

    public function updateUser(int $id, UpdateUserDto $dto): bool
    {
        return $this->userRepository->update($id, $dto);
    }

    public function deleteUser(int $userIdToDelete, int $currentUserId): bool
    {
        if ($userIdToDelete === $currentUserId) {
            throw ValidationException::withMessages([
                'user' => ['Нельзя удалить самого себя'],
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
