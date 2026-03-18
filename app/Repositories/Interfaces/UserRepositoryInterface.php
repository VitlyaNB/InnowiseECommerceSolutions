<?php

namespace App\Repositories\Interfaces;

use App\Dto\RegisterDto;
use App\Dto\UpdateUserDto;
use App\Dto\UserDto;

interface UserRepositoryInterface
{
    /** @return array<int, UserDto> */
    public function getAll(): array;

    public function findByEmail(string $email): ?UserDto;

    public function findById(int $id): ?UserDto;

    public function create(RegisterDto $data): UserDto;

    public function update(int $id, UpdateUserDto $data): bool;

    public function delete(int $id): bool;

    public function verifyCredentials(string $email, string $password): ?UserDto;

    public function deleteTokens(int $userId): void;

    public function topUp(int $userId, float $amount): UserDto;

    public function createToken(int $userId, string $tokenName): string;

    public function findByIdForUpdate(int $id): ?UserDto;

    public function decrementBalance(int $userId, float $amount): bool;
}
