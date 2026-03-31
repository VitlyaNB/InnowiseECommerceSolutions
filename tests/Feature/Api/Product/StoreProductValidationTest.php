<?php

namespace Tests\Feature\Api\Product;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreProductValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_product_requires_mandatory_fields(): void
    {
        $admin = User::factory()->createOne(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'category_id']);
    }

    public function test_store_product_fails_if_price_is_negative(): void
    {
        $admin = User::factory()->createOne(['role' => 'admin']);
        $category = Category::factory()->createOne();

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'Bad Price',
            'price' => -10,
            'category_id' => $category->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }
}
