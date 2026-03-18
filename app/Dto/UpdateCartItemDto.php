<?php

namespace App\Dto;

final readonly class UpdateCartItemDto extends BaseDto
{
    public function __construct(
        public int $quantity,
    ) {}
}
