<?php

namespace App\Repositories;

use App\Dto\ReviewDto;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

final class ReviewRepository implements ReviewRepositoryInterface
{
    public function canReview(int $userId, int $productId): bool
    {
        return OrderItem::query()
            ->where('product_id', $productId)
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->whereIn('status', ['paid', 'shipped']);
            })
            ->exists();
    }

    public function hasTopLevelReview(int $userId, int $productId): bool
    {
        return Review::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->whereNull('parent_id')
            ->exists();
    }

    /** @return array<int, ReviewDto> */
    public function getProductReviews(int $productId, ?int $viewerUserId = null): array
    {
        $collection = Review::query()
            ->withCount('likes')
            ->with(['replies' => function ($query) {
                $query->withCount('likes')->with('user');
            }, 'user'])
            ->where('product_id', $productId)
            ->whereNull('parent_id')
            ->orderByDesc('created_at')
            ->get();

        /** @var array<int, ReviewDto> $result */
        $result = [];
        foreach ($collection as $review) {
            /** @var Review $review */
            $result[] = $this->mapToDto($review, $viewerUserId);
        }

        return $result;
    }

    public function hasLike(int $userId, int $reviewId): bool
    {
        return ReviewLike::query()
            ->where('user_id', $userId)
            ->where('review_id', $reviewId)
            ->exists();
    }

    public function createLike(int $userId, int $reviewId): void
    {
        ReviewLike::query()->create([
            'user_id' => $userId,
            'review_id' => $reviewId,
        ]);
    }

    public function deleteLike(int $userId, int $reviewId): bool
    {
        return (bool) ReviewLike::query()
            ->where('user_id', $userId)
            ->where('review_id', $reviewId)
            ->delete();
    }

    public function create(ReviewDto $data): ReviewDto
    {
        /** @var Review $review */
        $review = Review::query()->create([
            'user_id' => $data->userId,
            'product_id' => $data->productId,
            'parent_id' => $data->parentId,
            'rating' => $data->rating,
            'comment' => $data->comment ?? '',
        ]);

        return $this->mapToDto($review);
    }

    private function mapToDto(Review $review, ?int $viewerUserId = null): ReviewDto
    {
        $replies = $review->relationLoaded('replies')
            ? $review->replies->map(function (Review $reply) use ($viewerUserId): ReviewDto {
                return new ReviewDto(
                    id: $reply->id,
                    userId: $reply->user_id,
                    productId: $reply->product_id,
                    parentId: $reply->parent_id,
                    rating: $reply->rating,
                    comment: $reply->comment,
                    likesCount: (int) ($reply->likes_count ?? $reply->likes()->count()),
                    isLiked: $viewerUserId !== null
                        ? $reply->likes()->where('user_id', $viewerUserId)->exists()
                        : false,
                );
            })->all()
            : [];

        return new ReviewDto(
            id: $review->id,
            userId: $review->user_id,
            productId: $review->product_id,
            parentId: $review->parent_id,
            rating: $review->rating,
            comment: $review->comment,
            likesCount: (int) ($review->likes_count ?? $review->likes()->count()),
            isLiked: $viewerUserId !== null
                ? $review->likes()->where('user_id', $viewerUserId)->exists()
                : false,
            replies: $replies,
        );
    }
}
