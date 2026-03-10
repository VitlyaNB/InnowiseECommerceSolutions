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
        return OrderItem::where('product_id', $productId)
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->whereIn('status', ['paid', 'shipped']);
            })
            ->exists();
    }

    public function createReview(int $userId, array $data): Review
    {
        if (empty($data['parent_id'])) {
            if (!$this->canReview($userId, $data['product_id'])) {
                throw new Exception('Вы можете оставлять отзывы только на купленные товары.');
            }

            $exists = Review::where('user_id', $userId)
                ->where('product_id', $data['product_id'])
                ->whereNull('parent_id')
                ->exists();

            if ($exists) {
                throw new Exception('Вы уже оставили отзыв на этот товар.');
            }
        }

        return Review::create([
            'user_id' => $userId,
            'product_id' => $data['product_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'rating' => $data['rating'] ?? null,
            'comment' => $data['comment'],
        ]);
    }

    public function toggleLike(int $userId, int $reviewId): bool
    {
        $like = ReviewLike::where('user_id', $userId)->where('review_id', $reviewId)->first();

        if ($like) {
            $like->delete();
            return false;
        } else {
            ReviewLike::create(['user_id' => $userId, 'review_id' => $reviewId]);
            return true;
        }
    }

    public function getProductReviews(int $productId)
    {
        return Review::where('product_id', $productId)
            ->whereNull('parent_id')
            ->with(['replies.user', 'replies.likes'])
            ->withCount('likes')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
                $review->is_liked = $review->isLiked;
                $review->replies->each(function($reply) {
                    $reply->likes_count = $reply->likes()->count();
                    $reply->is_liked = $reply->isLiked;
                });
                return $review;
            });
    }
}
