<?php

namespace App\Repositories\Interfaces;

use App\Models\Review;
use App\Models\ReviewLike;
use Illuminate\Support\Collection;

interface ReviewRepositoryInterface
{
    /** @return Collection<int, Review> */
    public function getProductReviews(int $productId): Collection;

    public function findLike(int $userId, int $reviewId): ?ReviewLike;

    public function createLike(int $userId, int $reviewId): ReviewLike;

    public function deleteLike(ReviewLike $like): bool;

    /** @param array<string, mixed> $data */
    public function create(array $data): Review;

    public function findById(int $id): ?Review;
}
