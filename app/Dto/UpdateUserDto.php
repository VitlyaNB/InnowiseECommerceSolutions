<?php

namespace App\Dto;

final readonly class UpdateUserDto extends BaseDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $role = null,
        public ?float $balance = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'balance' => $this->balance,
        ], fn ($value) => ! is_null($value));
    }
}
