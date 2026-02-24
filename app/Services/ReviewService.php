<?php

namespace App\Services;

use App\DTO\ReviewDTO;
use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class ReviewService
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository
    ) {}

    public function getProductReviews(int $productId): Collection
    {
        return $this->reviewRepository->getProductReviews($productId);
    }

    public function getReviewById(int $id): Review
    {
        $review = $this->reviewRepository->findById($id);

        if (!$review) {
            throw new ModelNotFoundException("Review with ID {$id} not found.");
        }

        return $review;
    }

    public function createReview(int $userId, ReviewDTO $data): Review
    {
        return $this->reviewRepository->create($userId, $data);
    }

    public function updateReview(int $userId, int $id, ReviewDTO $data): Review
    {
        $review = $this->getReviewById($id);
        
        if ($review->user_id !== $userId) {
            throw new \RuntimeException("Unauthorized to update this review.");
        }

        $this->reviewRepository->update($id, $data);

        return $this->getReviewById($id);
    }

    public function deleteReview(int $userId, int $id): bool
    {
        $review = $this->getReviewById($id);
        
        if ($review->user_id !== $userId) {
            throw new \RuntimeException("Unauthorized to delete this review.");
        }

        return $this->reviewRepository->delete($id);
    }
}
