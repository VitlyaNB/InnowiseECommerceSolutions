<?php

namespace Tests\Unit\Services;

use App\Dto\CartItemDto;
use App\Dto\ProductDto;
use App\Dto\TotalsDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\CartService;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    private CartItemRepositoryInterface $cartRepo;

    private CartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var CartItemRepositoryInterface&MockInterface $cartRepository */
        $cartRepository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->cartRepo = $cartRepository;
        $this->service = new CartService($this->cartRepo);
    }

    public function test_calculate_totals_returns_correct_values(): void
    {
        $product1 = new ProductDto(price: 100.0);
        $item1 = new CartItemDto(id: 1, productId: 1, quantity: 2, product: $product1);

        $product2 = new ProductDto(price: 50.0);
        $item2 = new CartItemDto(id: 2, productId: 2, quantity: 1, product: $product2);

        $items = [$item1, $item2];

        $reflection = new ReflectionClass(CartService::class);
        $method = $reflection->getMethod('calculateTotals');
        $method->setAccessible(true);

        /** @var TotalsDto $totals */
        $totals = $method->invoke($this->service, $items);

        $this->assertEquals(250, $totals->total);
        $this->assertEquals(250, $totals->subtotal);
        $this->assertEquals(0, $totals->tax);
    }
}
