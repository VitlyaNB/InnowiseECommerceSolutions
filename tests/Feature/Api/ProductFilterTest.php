<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_products_by_category(): void
    {
        $category = Category::factory()->createOne();
        $product1 = Product::factory()->createOne(['category_id' => $category->id]);
        $product2 = Product::factory()->createOne();

        $response = $this->getJson("/api/categories/{$category->id}/products");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product1->id])
            ->assertJsonMissing(['id' => $product2->id]);
    }
}
