<?php

namespace Tests\Feature\Services;

use App\Dto\OrderDto;
use App\Dto\UserDto;
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

    private function makeUserDto(User $user): UserDto
    {
        return new UserDto(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            role: $user->role,
            balance: (float) $user->balance,
        );
    }

    public function test_order_fails_if_insufficient_balance(): void
    {
        $user = User::factory()->createOne(['balance' => 0]);
        $product = Product::factory()->createOne(['price' => 1000, 'quantity' => 10]);

        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Недостаточно средств на кошельке.');

        $this->orderService->createOrder(
            $this->makeUserDto($user),
            new OrderDto(
                selectedItemIds: [$cartItem->id],
                shippingAddress: 'Test Street 1'
            )
        );
    }

    public function test_order_reduces_product_stock(): void
    {
        $user = User::factory()->createOne(['balance' => 1000]);
        $product = Product::factory()->createOne(['price' => 100, 'quantity' => 5]);

        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->orderService->createOrder(
            $this->makeUserDto($user),
            new OrderDto(
                selectedItemIds: [$cartItem->id],
                shippingAddress: 'Test Street 1'
            )
        );

        $this->assertEquals(3, $product->fresh()->quantity);
    }

    public function test_order_fails_when_part_of_selected_items_is_missing_in_user_cart(): void
    {
        $user = User::factory()->createOne(['balance' => 1000]);
        $product = Product::factory()->createOne(['price' => 100, 'quantity' => 5]);
        $userCartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $otherUser = User::factory()->createOne();
        $otherCartItem = CartItem::query()->create([
            'user_id' => $otherUser->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Некоторые выбранные товары недоступны в корзине пользователя');

        $this->orderService->createOrder(
            $this->makeUserDto($user),
            new OrderDto(
                selectedItemIds: [$userCartItem->id, $otherCartItem->id],
                shippingAddress: 'Test Street 1'
            )
        );
    }
}
