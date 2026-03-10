<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use App\DTO\RegisterDTO;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /** @return Collection<int, User> */
    public function getAll(): Collection;

    public function findByEmail(string $email): ?User;

    public function create(RegisterDTO $data): User;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
