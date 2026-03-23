<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

final class ExternalCategoryApiClient
{
    public function fetchCategories(): array
    {
        /** @var string|null $apiUrl */
        $apiUrl = config('services.external_project.api_url');

        /** @var string|null $apiKey */
        $apiKey = config('services.external_project.api_key');

        if (empty($apiUrl)) {
            throw new \RuntimeException('API URL не настроен. Проверьте конфигурацию системы.');
        }

        $headers = ['Accept' => 'application/json'];
        if (! empty($apiKey)) {
            $headers['Authorization'] = 'Bearer '.(string) $apiKey;
        }

        $response = Http::withHeaders($headers)->get((string) $apiUrl);

        if (! $response->successful()) {
            throw new \RuntimeException('Не удалось получить данные от внешнего сервиса.');
        }

        /** @var mixed $data */
        $data = $response->json();

        /** @var mixed $categories */
        $categories = is_array($data) ? ($data['data'] ?? $data['categories'] ?? $data) : [];

        if (! is_array($categories)) {
            throw new \RuntimeException('Некорректный формат данных от внешнего сервиса.');
        }

        return $categories;
    }
}
