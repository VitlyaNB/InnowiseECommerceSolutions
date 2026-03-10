<?php

namespace App\DTO;

use Illuminate\Http\Request;

final readonly class OrderDTO extends BaseDTO
{
    public function __construct(
        public string $shipping_address = '',
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new self(
            shipping_address: $request->string('shipping_address')->value(),
        );
    }
}
