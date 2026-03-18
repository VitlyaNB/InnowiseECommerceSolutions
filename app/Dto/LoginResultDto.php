<?php

namespace App\Dto;

final readonly class LoginResultDto extends BaseDto
{
    public function __construct(
        public UserDto $user,
        public string $token,
    ) {}
}
