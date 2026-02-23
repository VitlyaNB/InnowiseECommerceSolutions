<?php

namespace App\DTO;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseDTO
{

    public static function fromRequest(FormRequest $request): static
    {
        return new static(...$request->validated());
    }
}
