<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_search_returns_correct_results()
    {
        $product = Product::factory()->create(['name' => 'iPhone 15 Pro']);
        Product::factory()->create(['name' => 'Samsung Galaxy S23']);

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

        $response = $this->getJson('/api/products/search?query=iPhone');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'iPhone 15 Pro'])
            ->assertJsonMissing(['name' => 'Samsung Galaxy S23']);
    }

    public function test_search_with_empty_query_returns_all_or_error()
    {
        $mockHandler = new MockHandler([
            new Response(200, ['X-Elastic-Product' => 'Elasticsearch', 'Content-Type' => 'application/json'], json_encode([
                'hits' => [
                    'total' => ['value' => 0],
                    'hits' => []
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

        $response = $this->getJson('/api/products/search?query=');

        $response->assertStatus(200);
    }
}
