<?php

namespace App\DTO;

use Illuminate\Http\Request;

class LoginDTO extends BaseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): static
    {
        return new static(
            email: $request->validated('email'),
            password: $request->validated('password'),
        );
    }
}
