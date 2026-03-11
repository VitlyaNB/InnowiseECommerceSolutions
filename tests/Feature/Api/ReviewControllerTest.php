<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_leave_review_for_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        // Mock that user bought the product
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price
        ]);

        $response = $this->actingAs($user)->postJson("/api/reviews", [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Отличный товар!'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5
        ]);
    }

    public function test_user_can_toggle_like_on_review()
    {
        $user = User::factory()->create();
        
        // Create a product and a review
        $product = Product::factory()->create();
        $review = Review::query()->create([
            'user_id' => User::factory()->create()->id,
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Test Review'
        ]);

        // First like
        $response = $this->actingAs($user)->postJson("/api/reviews/{$review->id}/like");
        $response->assertStatus(200);
        $this->assertDatabaseHas('review_likes', ['user_id' => $user->id, 'review_id' => $review->id]);

        // Second call should unlike (toggle)
        $response = $this->actingAs($user)->postJson("/api/reviews/{$review->id}/like");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('review_likes', ['user_id' => $user->id, 'review_id' => $review->id]);
    }
}
