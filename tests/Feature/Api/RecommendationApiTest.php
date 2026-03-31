<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_recommendations_returns_valid_structure(): void
    {
        $product = Product::factory()->createOne();
        Product::factory()->count(3)->create(['category_id' => $product->category_id]);

        $response = $this->getJson("/api/products/{$product->id}/recommendations");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'also_bought',
                'similar',
                'recently_viewed',
            ]);
    }
}
