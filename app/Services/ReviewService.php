<?php

namespace App\Services;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Exception;

class ReviewService
{
    public function canReview(int $userId, int $productId): bool
    {
        return OrderItem::query()->where('product_id', $productId)
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->whereIn('status', ['paid', 'shipped']);
            })
            ->exists();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createReview(int $userId, array $data): Review
    {
        if (empty($data['parent_id'])) {
            $rawProductId = $data['product_id'] ?? 0;
            $productId = (int) (is_scalar($rawProductId) ? $rawProductId : 0);
            if (!$this->canReview($userId, $productId)) {
                throw new Exception('Вы можете оставлять отзывы только на купленные товары.');
            }

            $exists = Review::query()->where('user_id', $userId)
                ->where('product_id', $productId)
                ->whereNull('parent_id')
                ->exists();

            if ($exists) {
                throw new Exception('Вы уже оставили отзыв на этот товар.');
            }
        }

        $rawProductId = $data['product_id'] ?? 0;
        $rawComment = $data['comment'] ?? '';

        /** @var Review $review */
        $review = Review::query()->create([
            'user_id' => $userId,
            'product_id' => (int) (is_scalar($rawProductId) ? $rawProductId : 0),
            'parent_id' => isset($data['parent_id']) && is_scalar($data['parent_id']) ? (int) $data['parent_id'] : null,
            'rating' => isset($data['rating']) && is_scalar($data['rating']) ? (int) $data['rating'] : null,
            'comment' => (string) (is_scalar($rawComment) ? $rawComment : ''),
        ]);

        return $review;
    }

    public function toggleLike(int $userId, int $reviewId): bool
    {
        $like = ReviewLike::query()->where('user_id', $userId)->where('review_id', $reviewId)->first();

        if ($like) {
            $like->delete();
            return false;
        } else {
            ReviewLike::query()->create(['user_id' => $userId, 'review_id' => $reviewId]);
            return true;
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, Review>
     */
    public function getProductReviews(int $productId)
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Review> $reviews */
        $reviews = Review::query()
            ->withCount('likes')
            ->with(['user', 'replies.user', 'replies.likes'])
            ->where('product_id', $productId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return $reviews->map(function ($review) {
                /** @var Review $review */
                $review->is_liked = (bool) $review->isLiked;
                $review->replies->each(function($reply) {
                    /** @var Review $reply */
                    $reply->likes_count = (int) $reply->likes()->count();
                    $reply->is_liked = (bool) $reply->isLiked;
                });
                return $review;
            });
    }
}
