<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_average_rating_is_calculated_correctly(): void
    {
        $product = Product::factory()->createOne();
        $user1 = User::factory()->createOne();
        $user2 = User::factory()->createOne();

        foreach ([$user1, $user2] as $user) {
            $order = Order::factory()->createOne(['user_id' => $user->id, 'status' => 'paid']);
            OrderItem::factory()->createOne([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
            ]);
        }

        $this->actingAs($user1)->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'First',
        ])->assertStatus(201);

        $this->actingAs($user2)->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 1,
            'comment' => 'Second',
        ])->assertStatus(201);

        $avgRating = $product->reviews()->avg('rating');
        $this->assertEquals(3.0, (float) $avgRating);
    }
}
