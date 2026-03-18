<?php

namespace App\Dto;

abstract readonly class BaseDto
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
