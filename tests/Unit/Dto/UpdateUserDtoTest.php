<?php

namespace Tests\Unit\Dto;

use App\Dto\UpdateUserDto;
use Tests\TestCase;

class UpdateUserDtoTest extends TestCase
{
    public function test_update_user_to_array_filters_nulls(): void
    {
        $dto = new UpdateUserDto(name: 'New Name', email: null, role: 'admin', balance: 100.5);

        $this->assertSame([
            'name' => 'New Name',
            'role' => 'admin',
            'balance' => 100.5,
        ], $dto->toArray());
    }
}
