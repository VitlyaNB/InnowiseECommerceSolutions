<?php

namespace App\Services;

use App\Dto\CategoryDto;
use App\Dto\ExternalCategorySyncResultDto;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\Interfaces\ExternalCategorySyncServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class ExternalCategorySyncService implements ExternalCategorySyncServiceInterface
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function sync(): ExternalCategorySyncResultDto
    {
        /** @var string|null $apiUrl */
        $apiUrl = config('services.external_project.api_url');

        /** @var string|null $apiKey */
        $apiKey = config('services.external_project.api_key');

        if (empty($apiUrl)) {
            return new ExternalCategorySyncResultDto(
                ok: false,
                message: 'External project API URL is not configured. Set EXTERNAL_PROJECT_API_URL in .env',
                synced: 0,
                status: 400,
            );
        }

        try {
            $headers = ['Accept' => 'application/json'];
            if (! empty($apiKey)) {
                $headers['Authorization'] = 'Bearer '.(string) $apiKey;
            }

            $response = Http::withHeaders($headers)->get((string) $apiUrl);
            if (! $response->successful()) {
                return new ExternalCategorySyncResultDto(
                    ok: false,
                    message: 'Failed to fetch categories from external project',
                    synced: 0,
                    status: 502,
                );
            }

            /** @var mixed $data */
            $data = $response->json();

            /** @var mixed $categories */
            $categories = is_array($data) ? ($data['data'] ?? $data['categories'] ?? $data) : [];
            if (! is_array($categories)) {
                return new ExternalCategorySyncResultDto(
                    ok: false,
                    message: 'Invalid response format from external project',
                    synced: 0,
                    status: 422,
                );
            }

            $synced = 0;
            foreach ($categories as $item) {
                /** @var string|null $name */
                $name = is_array($item) ? ($item['name'] ?? $item['title'] ?? null) : (is_string($item) ? $item : null);
                if (empty($name)) {
                    continue;
                }

                if ($this->categoryRepository->existsByName($name)) {
                    continue;
                }

                $this->categoryRepository->create(new CategoryDto(name: $name));
                $synced++;
            }

            return new ExternalCategorySyncResultDto(
                ok: true,
                message: "Synced {$synced} new categories from external project",
                synced: $synced,
                status: 200,
            );
        } catch (Throwable $exception) {
            Log::error('External category sync failed: '.$exception->getMessage());

            return new ExternalCategorySyncResultDto(
                ok: false,
                message: 'Sync failed: '.$exception->getMessage(),
                synced: 0,
                status: 500,
            );
        }
    }
}
