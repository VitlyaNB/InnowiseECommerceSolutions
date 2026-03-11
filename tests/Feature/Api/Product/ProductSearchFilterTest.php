<?php

namespace Tests\Feature\Api\Product;

use App\Models\Product;
use App\Models\Category;
use Elastic\Elasticsearch\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class ProductSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_can_filter_by_price_range()
    {
        $target = Product::factory()->create(['name' => 'Cheap', 'price' => 50]);
        $other = Product::factory()->create(['name' => 'Expensive', 'price' => 500]);

        $mockClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $mockClient);

        $mockClient->shouldReceive('search')
            ->once()
            ->andReturn(new class($target) {
                public function __construct(private $product) {}
                public function asArray() {
                    return [
                        'hits' => [
                            'total' => ['value' => 1],
                            'hits' => [['_id' => (string) $this->product->id]]
                        ],
                        'aggregations' => [
                            'categories' => ['buckets' => []],
                            'min_price' => ['value' => 0],
                            'max_price' => ['value' => 1000]
                        ]
                    ];
                }
            });

        $response = $this->getJson('/api/products/search?query=Cheap&min_price=10&max_price=100');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Cheap'])
            ->assertJsonMissing(['name' => 'Expensive']);
    }

    public function test_search_can_filter_by_category()
    {
        $cat = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $cat->id, 'name' => 'Target']);
        $other = Product::factory()->create(['name' => 'Target']);

        $mockClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $mockClient);

        $mockClient->shouldReceive('search')
            ->once()
            ->andReturn(new class($product) {
                public function __construct(private $product) {}
                public function asArray() {
                    return [
                        'hits' => [
                            'total' => ['value' => 1],
                            'hits' => [['_id' => (string) $this->product->id]]
                        ],
                        'aggregations' => [
                            'categories' => ['buckets' => []],
                            'min_price' => ['value' => 0],
                            'max_price' => ['value' => 1000]
                        ]
                    ];
                }
            });

        $response = $this->getJson("/api/products/search?query=Target&categories[]={$cat->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product->id])
            ->assertJsonMissing(['id' => $other->id]);
    }
}
