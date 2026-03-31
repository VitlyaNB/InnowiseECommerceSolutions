<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_leave_review_for_product(): void
    {
        $user = User::factory()->createOne();
        $product = Product::factory()->createOne();

        $order = Order::factory()->createOne(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->createOne([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $response = $this->actingAs($user)->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Отличный товар!',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_user_can_toggle_like_on_review(): void
    {
        $user = User::factory()->createOne();

        $product = Product::factory()->createOne();
        $review = Review::query()->create([
            'user_id' => User::factory()->createOne()->id,
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Test Review',
        ]);

        $response = $this->actingAs($user)->postJson("/api/reviews/{$review->id}/like");
        $response->assertStatus(200);
        $this->assertDatabaseHas('review_likes', ['user_id' => $user->id, 'review_id' => $review->id]);

        $response = $this->actingAs($user)->postJson("/api/reviews/{$review->id}/like");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('review_likes', ['user_id' => $user->id, 'review_id' => $review->id]);
    }
}
