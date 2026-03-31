<?php

namespace Tests\Feature\Api\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetProductByIdActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_product_by_id(): void
    {
        $product = Product::factory()->createOne([
            'name' => 'Specific Gadget',
            'price' => 299.99,
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Specific Gadget')
            ->assertJsonPath('data.price', 299.99);
    }

    public function test_returns_404_for_non_existent_product(): void
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404);
    }
}
