<?php


namespace App\DTO;

use Illuminate\Http\Request;

class UpdateUserDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $role
    )
    {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->input('name'),
            $request->input('email'),
            $request->input('role')
        );
    }

    public function toArray(): array
    {
        // Убираем null значения, чтобы обновлять только переданные поля
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ], fn($value) => !is_null($value));
    }
}
