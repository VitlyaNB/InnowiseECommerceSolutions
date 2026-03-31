<?php

namespace Tests\Unit\Dto;

use App\Dto\ProductDto;
use Tests\TestCase;

class ProductDtoTest extends TestCase
{
    public function test_product_dto_to_array_uses_expected_keys(): void
    {
        $dto = new ProductDto(
            id: 1,
            name: 'Phone',
            description: 'Smartphone',
            price: 99.99,
            quantity: 5,
            categoryId: 3,
            isActive: true
        );

        $this->assertSame([
            'id' => 1,
            'name' => 'Phone',
            'description' => 'Smartphone',
            'price' => 99.99,
            'quantity' => 5,
            'category_id' => 3,
            'is_active' => true,
        ], $dto->toArray());
    }
}
