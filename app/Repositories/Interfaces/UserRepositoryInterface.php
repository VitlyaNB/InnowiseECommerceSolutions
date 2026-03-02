<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use App\DTO\RegisterDTO;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function getAll(): Collection;
    public function create(RegisterDTO $data): User;
    public function findByEmail(string $email): ?User;

    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
