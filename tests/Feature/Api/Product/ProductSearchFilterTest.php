<?php

namespace Tests\Feature\Api\Product;

use App\Models\Product;
use App\Models\Category;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_can_filter_by_price_range()
    {
        $target = Product::factory()->create(['name' => 'Cheap', 'price' => 50]);
        Product::factory()->create(['name' => 'Expensive', 'price' => 500]);

        $mockHandler = new MockHandler([
            new Response(200, ['X-Elastic-Product' => 'Elasticsearch', 'Content-Type' => 'application/json'], json_encode([
                'hits' => [
                    'total' => ['value' => 1],
                    'hits' => [['_id' => (string) $target->id]]
                ],
                'aggregations' => [
                    'categories' => ['buckets' => []],
                    'min_price' => ['value' => 0],
                    'max_price' => ['value' => 1000]
                ]
            ]))
        ]);
        $client = ClientBuilder::create()
            ->setHttpClient(new GuzzleClient(['handler' => $mockHandler]))
            ->build();
        $this->app->instance(Client::class, $client);

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

        $mockHandler = new MockHandler([
            new Response(200, ['X-Elastic-Product' => 'Elasticsearch', 'Content-Type' => 'application/json'], json_encode([
                'hits' => [
                    'total' => ['value' => 1],
                    'hits' => [['_id' => (string) $product->id]]
                ],
                'aggregations' => [
                    'categories' => ['buckets' => []],
                    'min_price' => ['value' => 0],
                    'max_price' => ['value' => 1000]
                ]
            ]))
        ]);
        $client = ClientBuilder::create()
            ->setHttpClient(new GuzzleClient(['handler' => $mockHandler]))
            ->build();
        $this->app->instance(Client::class, $client);

        $response = $this->getJson("/api/products/search?query=Target&categories[]={$cat->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product->id])
            ->assertJsonMissing(['id' => $other->id]);
    }
}
