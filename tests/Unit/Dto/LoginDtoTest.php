<?php

namespace Tests\Unit\Dto;

use App\Dto\LoginDto;
use Tests\TestCase;

class LoginDtoTest extends TestCase
{
    public function test_login_dto_maps_values(): void
    {
        $dto = new LoginDto(email: 'test@example.com', password: 'secret');

        $this->assertSame('test@example.com', $dto->email);
        $this->assertSame('secret', $dto->password);
    }
}
