<?php

namespace Tests\Unit\Dto;

use App\Dto\CartItemDto;
use Tests\TestCase;

class CartItemDtoTest extends TestCase
{
    public function test_cart_item_dto_maps_values(): void
    {
        $dto = new CartItemDto(id: 1, productId: 2, quantity: 3, userId: 4);

        $this->assertSame(2, $dto->productId);
        $this->assertSame(['id' => 1, 'productId' => 2, 'quantity' => 3, 'userId' => 4, 'product' => null], $dto->toArray());
    }
}
