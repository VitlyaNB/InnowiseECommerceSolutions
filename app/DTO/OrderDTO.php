<?php

namespace App\DTO;

use Illuminate\Http\Request;

class OrderDTO extends BaseDTO
{
    public function __construct(
        public readonly string $shipping_address,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            shipping_address: $request->validated('shipping_address'),
        );
    }
}
