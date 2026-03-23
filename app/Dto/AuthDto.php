<?php

namespace App\Dto;

final readonly class AuthDto extends BaseDto
{
    public function __construct(
        public UserDto $user,
        public string $token,
    ) {}
}
