<?php

namespace App\DTO;

use Illuminate\Http\Request;

abstract class BaseDTO
{
    abstract public static function fromRequest(Request $request): static;

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
