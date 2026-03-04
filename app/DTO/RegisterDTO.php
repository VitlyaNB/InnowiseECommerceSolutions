<?php

namespace App\DTO;

use Illuminate\Http\Request;

class RegisterDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new static(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
        );
    }
}
