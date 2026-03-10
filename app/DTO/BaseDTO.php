<?php

namespace App\DTO;

use Illuminate\Http\Request;

abstract readonly class BaseDTO
{
    abstract public static function fromRequest(Request $request): static;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
