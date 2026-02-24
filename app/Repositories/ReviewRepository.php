<?php

namespace App\Repositories;

use App\DTO\ReviewDTO;
use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Support\Collection;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function getProductReviews(int $productId): Collection
    {
        return Review::query()
            ->with('user:id,name')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $id): ?Review
    {
        return Review::find($id);
    }

    public function create(int $userId, ReviewDTO $data): Review
    {
        return Review::create([
            'user_id' => $userId,
            'product_id' => $data->product_id,
            'rating' => $data->rating,
            'comment' => $data->comment,
        ]);
    }

    public function update(int $id, ReviewDTO $data): bool
    {
        $review = Review::query()->findOrFail($id);
        
        return $review->update(array_filter([
            'rating' => $data->rating,
            'comment' => $data->comment,
        ], fn($value) => $value !== null));
    }

    public function delete(int $id): bool
    {
        $review = Review::query()->find($id);
        
        if (!$review) {
            return false;
        }

        return (bool) $review->delete();
    }
}
