<?php


namespace Tests\Unit\DTO;

use App\DTO\LoginDTO;
use Illuminate\Http\Request;
use Tests\TestCase;

class LoginDTOTest extends TestCase
{
    public function test_login_dto_from_request()
    {
        $data = [
            'email' => 'user@example.com',
            'password' => 'password123'
        ];

        $request = new Request($data);
        $dto = LoginDTO::fromRequest($request);

        $this->assertInstanceOf(LoginDTO::class, $dto);
        $this->assertEquals('user@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }
}
