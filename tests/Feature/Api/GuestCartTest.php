<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_item_to_cart_via_session(): void
    {
        $product = Product::factory()->createOne();
        $sessionId = 'test_session_123';

        $response = $this->withHeader('X-Session-Id', $sessionId)
            ->postJson('/api/cart', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'session_id' => $sessionId,
            'user_id' => null,
        ]);
    }
}
