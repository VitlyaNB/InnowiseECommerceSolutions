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
            $responseDto = $this->apiClient->fetchCategories();

            $synced = 0;
            foreach ($responseDto->categories as $item) {
                $name = $item->name;
                if ($this->categoryRepository->existsByName($name)) {
                    continue;
                }

                $this->categoryRepository->create(new CategoryDto(name: $name));
                $synced++;
            }

            return new ExternalCategorySyncResultDto(
                status: true,
                message: "Synchronized {$synced} new categories.",
                synced: $synced,
                httpStatus: 200,
            );
        } catch (Throwable) {
            return new ExternalCategorySyncResultDto(
                status: false,
                message: 'Failed to synchronize categories. Please try again later.',
                synced: 0,
                httpStatus: 500,
            );
        }
    }
}
