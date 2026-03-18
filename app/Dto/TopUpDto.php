<?php

namespace App\Dto;

final readonly class TopUpDto extends BaseDto
{
    public function __construct(
        public float $amount,
    ) {}
}
