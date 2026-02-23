<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use App\DTO\RegisterDTO;

interface UserRepositoryInterface
{
    public function create(RegisterDTO $data): User;

    public function findByEmail(string $email): ?User;
}
