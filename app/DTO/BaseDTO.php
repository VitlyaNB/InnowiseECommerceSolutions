<?php


namespace App\DTO;

abstract class BaseDTO
{
    public static function fromRequest($request): static
    {
        return new static(...$request->validated());
    }
}
