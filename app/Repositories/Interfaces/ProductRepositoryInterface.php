<?php

namespace App\Repositories\Interfaces;

use App\Dto\PaginatedResultDto;
use App\Dto\ProductDto;
use App\Dto\ProductFiltersDto;
use App\Dto\ProductIdsQueryDto;
use App\Dto\RandomProductsQueryDto;

interface ProductRepositoryInterface
{
    public function getAll(ProductFiltersDto $filters, int $perPage = 15): PaginatedResultDto;

    /** @return array<int, ProductDto> */
    public function getByCategory(int $categoryId): array;

    public function findById(int $id): ?ProductDto;

    public function create(ProductDto $data): ProductDto;

    public function update(int $id, ProductDto $data): bool;

    public function delete(int $id): bool;

    public function saveImage(int $productId, string $imagePath): void;

    public function deleteImages(int $productId): void;

    /** @return array<int, ProductDto> */
    public function getByIds(ProductIdsQueryDto $query): array;

    /** @return array<int, int> */
    public function getAlsoBoughtProductIds(int $productId, int $limit): array;

    /** @return array<int, int> */
    public function getSimilarFallbackIds(int $categoryId, int $excludedProductId, int $limit): array;

    /** @return array<int, int> */
    public function getRandomActiveProductIds(RandomProductsQueryDto $query): array;

    public function decrementStock(int $productId, int $quantity): bool;
}
