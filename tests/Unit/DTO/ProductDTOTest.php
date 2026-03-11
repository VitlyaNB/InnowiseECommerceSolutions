<?php

namespace Tests\Unit\DTO;

use App\DTO\ProductDTO;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductDTOTest extends TestCase
{
    public function test_product_dto_from_request_mapping()
    {
        $data = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'quantity' => 10,
            'category_id' => 1
        ];

        $request = new Request($data);
        $dto = ProductDTO::fromRequest($request);

        $this->assertInstanceOf(ProductDTO::class, $dto);
        $this->assertEquals($data['name'], $dto->name);
        $this->assertEquals($data['price'], $dto->price);
        $this->assertEquals($data['category_id'], $dto->category_id);
        $this->assertEquals($data['quantity'], $dto->quantity);
    }
}
