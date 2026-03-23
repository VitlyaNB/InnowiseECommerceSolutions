<?php

namespace App\Repositories\Interfaces;

use App\Dto\OrderDetailsDto;
use App\Dto\OrderItemDto;

interface OrderRepositoryInterface
{
    public function create(int $userId, float $totalAmount, string $shippingAddress, string $status = 'paid'): OrderDetailsDto;

    public function createItem(int $orderId, OrderItemDto $item): void;

    public function findByIdWithItems(int $orderId): ?OrderDetailsDto;

    /** @return array<int, OrderDetailsDto> */
    public function getByUserId(int $userId): array;
}
