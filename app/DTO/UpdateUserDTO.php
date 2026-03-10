<?php


namespace App\DTO;

use Illuminate\Http\Request;

readonly class UpdateUserDTO
{
    public function __construct(
        public ?string $name,
        public ?string $email,
        public ?string $role
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
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ], fn($value) => !is_null($value));
    }
}
