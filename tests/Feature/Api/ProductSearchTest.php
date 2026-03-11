<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Elastic\Elasticsearch\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_search_returns_correct_results()
    {
        $product = Product::factory()->create(['name' => 'iPhone 15 Pro']);
        Product::factory()->create(['name' => 'Samsung Galaxy S23']);

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

        $response = $this->getJson('/api/products/search?query=iPhone');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'iPhone 15 Pro'])
            ->assertJsonMissing(['name' => 'Samsung Galaxy S23']);
    }

    public function test_search_with_empty_query_returns_all_or_error()
    {
        $mockClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $mockClient);

        $mockClient->shouldReceive('search')
            ->once()
            ->andReturn(new class() {
                public function asArray() {
                    return [
                        'hits' => [
                            'total' => ['value' => 0],
                            'hits' => []
                        ],
                        'aggregations' => [
                            'categories' => ['buckets' => []],
                            'min_price' => ['value' => 0],
                            'max_price' => ['value' => 1000]
                        ]
                    ];
                }
            });

        $response = $this->getJson('/api/products/search?query=');

        $response->assertStatus(200);
    }
}
