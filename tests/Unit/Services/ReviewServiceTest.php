<?php

namespace Tests\Unit\Services;

use App\Models\Review;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_review()
    {
        $user = \App\Models\User::factory()->create();
        $product = \App\Models\Product::factory()->create();
        
        // Mock that user bought the product
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price
        ]);

        $service = new ReviewService();

        $data = [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Excellent!'
        ];

        $result = $service->createReview($user->id, $data);

        $this->assertInstanceOf(Review::class, $result);
        $this->assertEquals(5, $result->rating);
        $this->assertEquals('Excellent!', $result->comment);
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5
        ]);
    }
}
