<?php

namespace App\Services;

use App\Dto\AuthDto;
use App\Dto\LoginDto;
use App\Dto\RegisterDto;
use App\Dto\UserDto;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\AuthTokenServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthTokenServiceInterface $authTokenService,
    ) {}

    public function register(RegisterDto $dto): AuthDto
    {
        $userDto = $this->userRepository->create($dto);
        $token = $this->authTokenService->createForUserId($userDto->id);

        return new AuthDto(user: $userDto, token: $token);
    }

    public function login(LoginDto $dto): AuthDto
    {
        $userDto = $this->verifyCredentials($dto->email, $dto->password);

        if (! $userDto) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = $this->authTokenService->createForUserId($userDto->id);

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

    public function deleteUser(int $userIdToDelete, int $currentUserId): bool
    {
        if ($userIdToDelete === $currentUserId) {
            throw ValidationException::withMessages([
                'user' => ['You cannot delete your own account.'],
            ]);
        }

        return $this->userRepository->delete($userIdToDelete);
    }
}
