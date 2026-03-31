<?php

namespace Tests\Feature\Api;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_product_to_cart(): void
    {
        $user = User::factory()->createOne();
        $product = Product::factory()->createOne(['quantity' => 10]);

        $response = $this->actingAs($user)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_can_update_cart_item_quantity(): void
    {
        $user = User::factory()->createOne();
        $product = Product::factory()->createOne();
        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->putJson("/api/cart/{$cartItem->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(5, $cartItem->fresh()->quantity);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $user = User::factory()->createOne();
        $product = Product::factory()->createOne();
        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/cart/{$cartItem->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}
