<?php

namespace App\Dto;

final readonly class UserDto extends BaseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
        public float $balance,
        public ?string $emailVerifiedAt = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $passwordHash = null,
    ) {}
}
