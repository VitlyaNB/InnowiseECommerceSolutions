<?php

namespace App\Infrastructure\Persistence;

use App\Infrastructure\Interfaces\ElasticsearchClientInterface;
use Elastic\Elasticsearch\Client;

final readonly class ElasticsearchClientWrapper implements ElasticsearchClientInterface
{
    public function __construct(
        private Client $client
    ) {}

    public function search(array $params): array
    {
        return $this->client->search($params)->asArray();
    }

    public function index(array $params): array
    {
        return $this->client->index($params)->asArray();
    }

    public function delete(array $params): array
    {
        return $this->client->delete($params)->asArray();
    }
}
