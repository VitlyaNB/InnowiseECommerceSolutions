<?php

namespace Tests\Unit\DTO;

use App\DTO\UpdateUserDTO;
use Illuminate\Http\Request;
use Tests\TestCase;

class UpdateUserDTOTest extends TestCase
{
    public function test_update_user_dto_from_request()
    {
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $request = new Request($data);
        $dto = UpdateUserDTO::fromRequest($request);

        $this->assertInstanceOf(UpdateUserDTO::class, $dto);
        $this->assertEquals($data['name'], $dto->name);
        $this->assertEquals($data['email'], $dto->email);
    }
}
