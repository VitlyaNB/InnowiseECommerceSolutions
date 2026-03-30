<?php

namespace Tests\Unit\Services;

use App\Dto\RegisterDto;
use App\Dto\UserDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\AuthService;
use App\Services\Interfaces\AuthTokenServiceInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface|MockInterface $userRepository;

    private CartItemRepositoryInterface|MockInterface $cartRepository;

    private AuthTokenServiceInterface|MockInterface $authTokenService;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->cartRepository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->authTokenService = Mockery::mock(AuthTokenServiceInterface::class);
        $this->authService = new AuthService($this->userRepository, $this->cartRepository, $this->authTokenService);
    }

    public function test_it_registers_a_new_user(): void
    {
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

        $this->userRepository->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn($userDto);

        $this->authTokenService->shouldReceive('createForUserId')
            ->once()
            ->with(1)
            ->andReturn('test-token');

        $result = $this->authService->register($dto);

        $this->assertSame(1, $result->user->id);
        $this->assertSame('test-token', $result->token);
    }
}
