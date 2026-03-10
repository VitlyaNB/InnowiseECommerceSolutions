<?php

namespace App\DTO;

use Illuminate\Http\Request;

final readonly class LoginDTO extends BaseDTO
{
    public function __construct(
        public string $email = '',
        public string $password = '',
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new self(
            email: $request->string('email')->value(),
            password: $request->string('password')->value(),
        );
    }
}
