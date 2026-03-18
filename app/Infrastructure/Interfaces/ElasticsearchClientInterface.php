<?php

namespace App\Infrastructure\Interfaces;

interface ElasticsearchClientInterface
{
    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function search(array $params): array;

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function index(array $params): array;

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function delete(array $params): array;
}
