<?php

namespace App\Dto;

final readonly class CookieConsentDto extends BaseDto
{
    public function __construct(
        public bool $accepted,
    ) {}
}
