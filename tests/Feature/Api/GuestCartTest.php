<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_add_item_to_cart(): void
    {
        $product = Product::factory()->createOne();

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(401);
    }
}
