<?php

namespace App\Dto;

final readonly class RegisterDto extends BaseDto
{
    public function __construct(
        public string $name = '',
        public string $email = '',
        public string $password = '',
    ) {}
}
