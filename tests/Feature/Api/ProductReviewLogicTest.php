<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_average_rating_is_calculated_correctly()
    {
        $product = Product::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Mock that users bought the product
        foreach ([$user1, $user2] as $user) {
            $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price
            ]);
        }

        $this->actingAs($user1)->postJson("/api/reviews", [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'First'
        ]);

        $this->actingAs($user2)->postJson("/api/reviews", [
            'product_id' => $product->id,
            'rating' => 1,
            'comment' => 'Second'
        ]);

        $avgRating = $product->reviews()->avg('rating');
        $this->assertEquals(3.0, (float) $avgRating);
    }
}
