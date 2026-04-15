<?php

namespace App\Infrastructure\Persistence;

use App\Infrastructure\Interfaces\ElasticsearchClientInterface;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;

final readonly class ElasticsearchClientWrapper implements ElasticsearchClientInterface
{
    public function __construct(
        private Client $client
    ) {}

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function search(array $params): array
    {
        /** @var array<string, mixed> $searchParams */
        $searchParams = $params;

        try {
            /** @var Elasticsearch $response */
            $response = $this->client->search($searchParams);
        } catch (ClientResponseException $e) {
            return [];
        }

        /** @var array<string, mixed> $result */
        $result = $response->asArray();

        return $result;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function index(array $params): array
    {
        try {
            /** @var Elasticsearch $response */
            $response = $this->client->index($params);
        } catch (ClientResponseException $e) {
            return [];
        }

        /** @var array<string, mixed> $result */
        $result = $response->asArray();

        return $result;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function delete(array $params): array
    {
        /** @var array<string, mixed> $deleteParams */
        $deleteParams = $params;

        try {
            /** @var Elasticsearch $response */
            $response = $this->client->delete($deleteParams);
        } catch (ClientResponseException $e) {
            return [];
        }

        /** @var array<string, mixed> $result */
        $result = $response->asArray();

        return $result;
    }
}
