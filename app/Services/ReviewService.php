<?php

namespace App\Services;

use App\Dto\ReviewDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use RuntimeException;

final readonly class ReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}

    public function canReview(int $userId, int $productId): bool
    {
        return $this->reviewRepository->canReview($userId, $productId);
    }

    public function createReview(int $userId, ReviewDto $data): ReviewDto
    {
        if ($data->parentId === null) {
            if (! $this->reviewRepository->canReview($userId, $data->productId)) {
                throw new RuntimeException('You can review only purchased products.');
            }

            if ($this->reviewRepository->hasTopLevelReview($userId, $data->productId)) {
                throw new RuntimeException('You already left a review for this product.');
            }
        }

        return $this->reviewRepository->create(new ReviewDto(
            userId: $userId,
            productId: $data->productId,
            parentId: $data->parentId,
            rating: $data->rating,
            comment: $data->comment,
        ));
    }

    public function toggleLike(int $userId, int $reviewId): bool
    {
        if ($this->reviewRepository->hasLike($userId, $reviewId)) {
            $this->reviewRepository->deleteLike($userId, $reviewId);

            return false;
        }

        $this->reviewRepository->createLike($userId, $reviewId);

        return true;
    }
}
