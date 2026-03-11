<?php

namespace Tests\Unit\DTO;

use App\DTO\CategoryDTO;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CategoryDTOTest extends TestCase
{
    public function test_category_dto_from_request()
    {
        $data = [
            'name' => 'Home & Garden',
        ];

        $request = new Request($data);
        $dto = CategoryDTO::fromRequest($request);

        $this->assertInstanceOf(CategoryDTO::class, $dto);
        $this->assertEquals('Home & Garden', $dto->name);
        $this->assertNull($dto->image);
    }

    public function test_category_dto_instantiation()
    {
        $dto = new CategoryDTO(
            name: 'Toys',
            image: null
        );

        $this->assertEquals('Toys', $dto->name);
        $this->assertNull($dto->image);
    }
}
