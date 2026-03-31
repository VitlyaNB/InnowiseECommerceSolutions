<?php

namespace App\Services;

use App\Dto\CategoryDto;
use App\Dto\ExternalCategorySyncResultDto;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Throwable;

final readonly class ExternalCategorySyncService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private ExternalCategoryApiClient $apiClient
    ) {}

    public function sync(): ExternalCategorySyncResultDto
    {
        try {
            $categories = $this->apiClient->fetchCategories();

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
                message: "Синхронизировано {$synced} новых категорий.",
                synced: $synced,
                status: 200,
            );
        } catch (Throwable) {
            return new ExternalCategorySyncResultDto(
                ok: false,
                message: 'Не удалось синхронизировать категории. Попробуйте позже.',
                synced: 0,
                status: 500,
            );
        }
    }
}
