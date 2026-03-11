<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use App\DTO\RegisterDTO;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Tests\TestCase;
use Mockery;

class AuthServiceTest extends TestCase
{
    public function test_it_registers_a_new_user()
    {
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $authService = new AuthService($userRepository);

        $dto = new RegisterDTO(
            name: 'Test User',
            email: 'test@example.com',
            password: 'secret123'
        );

        $userMock = Mockery::mock(User::class)->makePartial();
        
        // Mock the token result
        $tokenMock = new class {
            public string $plainTextToken = 'test-token';
        };
        
        $userMock->shouldReceive('createToken')
            ->once()
            ->with('auth_token')
            ->andReturn($tokenMock);

        $userRepository->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn($userMock);

        $result = $authService->register($dto);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals('test-token', $result['token']);
    }
}
