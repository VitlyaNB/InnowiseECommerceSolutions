<?php

namespace Tests\Unit\Services;

use App\Dto\RegisterDto;
use App\Dto\UserDto;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\AuthService;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_it_registers_a_new_user(): void
    {
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $authService = new AuthService($userRepository);

        $dto = new RegisterDto(
            name: 'Test User',
            email: 'test@example.com',
            password: 'secret123'
        );

        $userDto = new UserDto(
            id: 1,
            name: 'Test User',
            email: 'test@example.com',
            role: 'user',
            balance: 0.0,
        );

        $userRepository->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn($userDto);

        $userRepository->shouldReceive('createToken')
            ->once()
            ->with(1, 'auth_token')
            ->andReturn('test-token');

        $result = $authService->register($dto);

        $this->assertSame(1, $result->user->id);
        $this->assertSame('test-token', $result->token);
    }
}
