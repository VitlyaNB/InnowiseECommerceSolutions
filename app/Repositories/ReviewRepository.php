<?php

namespace App\Repositories;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Support\Collection;

class ReviewRepository implements ReviewRepositoryInterface
{
    /** @return Collection<int, Review> */
    public function getProductReviews(int $productId): Collection
    {
        /** @var Collection<int, Review> $reviews */
        $reviews = Review::where('product_id', $productId)
            ->with(['user', 'likes'])
            ->latest()
            ->get();
        return $reviews;
    }

    public function findLike(int $userId, int $reviewId): ?ReviewLike
    {
        /** @var ReviewLike|null $like */
        $like = ReviewLike::where('user_id', $userId)
            ->where('review_id', $reviewId)
            ->first();
        return $like;
    }

    public function createLike(int $userId, int $reviewId): ReviewLike
    {
        return ReviewLike::create([
            'user_id' => $userId,
            'review_id' => $reviewId,
        ]);
    }

    public function deleteLike(ReviewLike $like): bool
    {
        return (bool) $like->delete();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    public function findById(int $id): ?Review
    {
        /** @var Review|null $review */
        $review = Review::find($id);
        return $review;
    }
}
