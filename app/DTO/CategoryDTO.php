<?php

namespace App\DTO;

use Illuminate\Http\Request;

class CategoryDTO
{
    public function __construct(
        public readonly string $name,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
        );
    }

    public function toArray(): array
    {
        return ['name' => $this->name];
    }
}
