<?php

namespace Tests\Unit\Services;

use App\Dto\RegisterDto;
use App\Dto\UserDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\AuthService;
use App\Services\Interfaces\AuthTokenServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;

    private CartItemRepositoryInterface&MockObject $cartRepository;

    private AuthTokenServiceInterface&MockObject $authTokenService;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->cartRepository = $this->createMock(CartItemRepositoryInterface::class);
        $this->authTokenService = $this->createMock(AuthTokenServiceInterface::class);
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

        $this->userRepository
            ->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($userDto);

        $this->authTokenService
            ->expects($this->once())
            ->method('createForUserId')
            ->with(1)
            ->willReturn('test-token');

        $result = $this->authService->register($dto);

        $this->assertSame(1, $result->user->id);
        $this->assertSame('test-token', $result->token);
    }
}
