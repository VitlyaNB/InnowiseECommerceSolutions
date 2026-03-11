<?php

namespace Tests\Feature\Api\Product;

use App\Models\Product;
use Elastic\Elasticsearch\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class ElasticsearchSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_endpoint_returns_paginated_results()
    {
        $product1 = Product::factory()->create(['name' => 'Laravel Framework']);
        $product2 = Product::factory()->create(['name' => 'React Library']);

        $mockClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $mockClient);

        $mockClient->shouldReceive('search')
            ->once()
            ->andReturn(new class($product1) {
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

        $response = $this->getJson('/api/products/search?query=Laravel');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price']
                ],
                'meta',
                'filters'
            ]);
    }

    public function test_search_results_filtering()
    {
        $target = Product::factory()->create(['name' => 'Special Keyboard']);
        $other = Product::factory()->create(['name' => 'Mouse']);

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

        $response = $this->getJson('/api/products/search?query=Keyboard');

        $response->assertJsonFragment(['name' => 'Special Keyboard']);
        $response->assertJsonMissing(['name' => 'Mouse']);
    }
}
