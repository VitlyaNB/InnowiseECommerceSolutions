<?php

namespace App\DTO;

use Illuminate\Http\Request;

final readonly class RegisterDTO extends BaseDTO
{
    public function __construct(
        public string $name = '',
        public string $email = '',
        public string $password = '',
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new self(
            name: $request->string('name')->value(),
            email: $request->string('email')->value(),
            password: $request->string('password')->value(),
        );
    }
}
