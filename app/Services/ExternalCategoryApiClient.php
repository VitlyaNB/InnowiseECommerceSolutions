<?php

namespace App\Services;

use App\Dto\ExternalCategoryApiResponseDto;
use App\Dto\ExternalCategoryItemDto;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class ExternalCategoryApiClient
{
    public function fetchCategories(): ExternalCategoryApiResponseDto
    {
        /** @var string|null $apiUrl */
        $apiUrl = config('services.external_project.api_url');

        /** @var string|null $apiKey */
        $apiKey = config('services.external_project.api_key');

        if (empty($apiUrl)) {
            throw new RuntimeException('API URL is not configured. Check system configuration.');
        }

        $headers = ['Accept' => 'application/json'];
        if (! empty($apiKey)) {
            $headers['Authorization'] = 'Bearer '.(string) $apiKey;
        }

        $response = Http::withHeaders($headers)->get((string) $apiUrl);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch data from external service.');
        }

        /** @var mixed $data */
        $data = $response->json();

        /** @var mixed $categories */
        $categories = is_array($data) ? ($data['data'] ?? $data['categories'] ?? $data) : [];

        if (! is_array($categories)) {
            throw new RuntimeException('Invalid external service data format.');
        }

        /** @var array<int, ExternalCategoryItemDto> $items */
        $items = [];
        foreach ($categories as $item) {
            /** @var string|null $name */
            $name = is_array($item) ? ($item['name'] ?? $item['title'] ?? null) : (is_string($item) ? $item : null);
            if (! is_string($name) || $name === '') {
                continue;
            }
            $items[] = new ExternalCategoryItemDto($name);
        }

        return new ExternalCategoryApiResponseDto(categories: $items);
    }
}
