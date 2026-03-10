<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalCategorySyncService
{
    public function sync(): array
    {
        $apiUrl = config('services.external_project.api_url');
        $apiKey = config('services.external_project.api_key');

        if (empty($apiUrl)) {
            return [
                'ok' => false,
                'message' => 'External project API URL is not configured. Set EXTERNAL_PROJECT_API_URL in .env',
                'synced' => 0,
                'status' => 400,
            ];
        }

        try {
            $headers = ['Accept' => 'application/json'];
            if (!empty($apiKey)) {
                $headers['Authorization'] = 'Bearer ' . $apiKey;
            }

            $response = Http::withHeaders($headers)->get($apiUrl);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'Failed to fetch categories from external project',
                    'synced' => 0,
                    'status' => 502,
                ];
            }

            $data = $response->json();
            $categories = $data['data'] ?? $data['categories'] ?? $data ?? [];

            if (!is_array($categories)) {
                return [
                    'ok' => false,
                    'message' => 'Invalid response format from external project',
                    'synced' => 0,
                    'status' => 422,
                ];
            }

            $synced = 0;

            foreach ($categories as $item) {
                $name = is_array($item) ? ($item['name'] ?? $item['title'] ?? null) : (is_string($item) ? $item : null);

                if (empty($name)) {
                    continue;
                }

                $existing = Category::where('name', $name)->first();

                if (!$existing) {
                    Category::create(['name' => $name]);
                    $synced++;
                }
            }

            return [
                'ok' => true,
                'message' => "Synced {$synced} new categories from external project",
                'synced' => $synced,
                'status' => 200,
            ];
        } catch (\Throwable $e) {
            Log::error('External category sync failed: ' . $e->getMessage());

            return [
                'ok' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
                'synced' => 0,
                'status' => 500,
            ];
        }
    }
}
