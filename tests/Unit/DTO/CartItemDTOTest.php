<?php

namespace Tests\Unit\DTO;

use App\DTO\CartItemDTO;
use Illuminate\Http\Request;
use Tests\TestCase;

class CartItemDTOTest extends TestCase
{
    public function test_cart_item_dto_from_request()
    {
        $data = [
            'product_id' => 10,
            'quantity' => 3
        ];

        $request = new Request($data);
        $dto = CartItemDTO::fromRequest($request);

        $this->assertInstanceOf(CartItemDTO::class, $dto);
        $this->assertEquals(10, $dto->product_id);
        $this->assertEquals(3, $dto->quantity);
    }

    public function test_cart_item_dto_default_quantity()
    {
        $dto = new CartItemDTO(
            product_id: 5
        );

        $this->assertEquals(1, $dto->quantity);
    }
}
