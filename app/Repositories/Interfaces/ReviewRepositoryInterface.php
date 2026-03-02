<?php

namespace App\Repositories\Interfaces;

use App\DTO\ReviewDTO;
use App\Models\Review;
use Illuminate\Support\Collection;

interface ReviewRepositoryInterface
{
    public function getProductReviews(int $productId): Collection;

    public function findById(int $id): ?Review;

    public function create(int $userId, ReviewDTO $data): Review;

    public function update(int $id, ReviewDTO $data): bool;

    public function delete(int $id): bool;
}
