<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductViewTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewing_product_records_interaction()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->postJson("/api/products/{$product->id}/view");

        $this->assertDatabaseHas('product_views', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_guest_viewing_product_records_interaction_without_user_id()
    {
        $product = Product::factory()->create();

        $this->postJson("/api/products/{$product->id}/view");

        $this->assertDatabaseHas('product_views', [
            'product_id' => $product->id,
            'user_id' => null,
        ]);
    }
}
