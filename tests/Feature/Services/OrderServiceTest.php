<?php

namespace Tests\Feature\Services;

use App\Dto\OrderDto;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
    }

    public function test_order_fails_if_insufficient_balance()
    {
        $user = User::factory()->create(['balance' => 0]);
        $product = Product::factory()->create(['price' => 1000, 'quantity' => 10]);

        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Недостаточно средств на кошельке.');

        $this->orderService->createOrder(
            $user->id,
            new OrderDto(
                selectedItemIds: [$cartItem->id],
                shippingAddress: 'Test Street 1'
            )
        );
    }

    public function test_order_reduces_product_stock()
    {
        $user = User::factory()->create(['balance' => 1000]);
        $product = Product::factory()->create(['price' => 100, 'quantity' => 5]);

        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->orderService->createOrder(
            $user->id,
            new OrderDto(
                selectedItemIds: [$cartItem->id],
                shippingAddress: 'Test Street 1'
            )
        );

        $this->assertEquals(3, $product->fresh()->quantity);
    }
}
