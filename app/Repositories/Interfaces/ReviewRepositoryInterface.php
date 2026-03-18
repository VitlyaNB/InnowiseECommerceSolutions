<?php

namespace App\Repositories\Interfaces;

use App\Dto\ReviewDto;

interface ReviewRepositoryInterface
{
    public function canReview(int $userId, int $productId): bool;

    public function hasTopLevelReview(int $userId, int $productId): bool;

    /** @return array<int, ReviewDto> */
    public function getProductReviews(int $productId, ?int $viewerUserId = null): array;

    public function hasLike(int $userId, int $reviewId): bool;

    public function createLike(int $userId, int $reviewId): void;

    public function deleteLike(int $userId, int $reviewId): bool;

    public function create(ReviewDto $data): ReviewDto;
}
