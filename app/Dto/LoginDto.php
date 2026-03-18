<?php

namespace App\Dto;

final readonly class LoginDto extends BaseDto
{
    public function __construct(
        public string $email = '',
        public string $password = '',
    ) {}
}
