<?php

namespace Tests\Unit\Dto;

use App\Dto\CategoryDto;
use Tests\TestCase;

class CategoryDtoTest extends TestCase
{
    public function test_category_dto_maps_values(): void
    {
        $dto = new CategoryDto(id: 1, name: 'Tech', imagePath: 'categories/tech.png');

        $this->assertSame(1, $dto->id);
        $this->assertSame('Tech', $dto->name);
        $this->assertSame('categories/tech.png', $dto->imagePath);
    }
}
