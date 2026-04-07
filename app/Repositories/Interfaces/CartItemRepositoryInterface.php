<?php

namespace App\Repositories\Interfaces;

use App\Dto\CartItemDto;
use App\Dto\SelectedIdsDto;

interface CartItemRepositoryInterface
{
    /** @return array<int, CartItemDto> */
    public function getByUser(int $userId): array;

    /** @return array<int, CartItemDto> */
    public function getSelectedByUser(int $userId, SelectedIdsDto $ids): array;

    public function deleteSelectedByUser(int $userId, SelectedIdsDto $ids): bool;

    public function findById(int $id): ?CartItemDto;

    public function findByUserAndProduct(int $userId, int $productId): ?CartItemDto;

    public function create(CartItemDto $data): CartItemDto;

    public function updateQuantity(int $id, int $quantity): bool;

    public function delete(int $id): bool;

    public function clearByUser(int $userId): bool;
}
