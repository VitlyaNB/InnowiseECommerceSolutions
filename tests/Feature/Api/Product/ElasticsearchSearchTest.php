<?php

namespace Tests\Feature\Api\Product;

use App\Models\Product;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElasticsearchSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_endpoint_returns_paginated_results(): void
    {
        $product1 = Product::factory()->createOne(['name' => 'Laravel Framework']);
        Product::factory()->createOne(['name' => 'React Library']);

        $mockHandler = new MockHandler([
            new Response(200, ['X-Elastic-Product' => 'Elasticsearch', 'Content-Type' => 'application/json'], json_encode([
                'hits' => [
                    'total' => ['value' => 1],
                    'hits' => [['_id' => (string) $product1->id]],
                ],
                'aggregations' => [
                    'categories' => ['buckets' => []],
                    'min_price' => ['value' => 0],
                    'max_price' => ['value' => 1000],
                ],
            ])),
        ]);
        $client = ClientBuilder::create()
            ->setHttpClient(new GuzzleClient(['handler' => $mockHandler]))
            ->build();
        $this->app->instance(Client::class, $client);

        $response = $this->getJson('/api/products/search?query=Laravel');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price'],
                ],
                'meta',
                'filters',
            ]);
    }

    public function test_search_results_filtering(): void
    {
        $target = Product::factory()->createOne(['name' => 'Special Keyboard']);
        Product::factory()->createOne(['name' => 'Mouse']);

        $mockHandler = new MockHandler([
            new Response(200, ['X-Elastic-Product' => 'Elasticsearch', 'Content-Type' => 'application/json'], json_encode([
                'hits' => [
                    'total' => ['value' => 1],
                    'hits' => [['_id' => (string) $target->id]],
                ],
                'aggregations' => [
                    'categories' => ['buckets' => []],
                    'min_price' => ['value' => 0],
                    'max_price' => ['value' => 1000],
                ],
            ])),
        ]);
        $client = ClientBuilder::create()
            ->setHttpClient(new GuzzleClient(['handler' => $mockHandler]))
            ->build();
        $this->app->instance(Client::class, $client);

        $response = $this->getJson('/api/products/search?query=Keyboard');

        $response->assertJsonFragment(['name' => 'Special Keyboard']);
        $response->assertJsonMissing(['name' => 'Mouse']);
    }
}
