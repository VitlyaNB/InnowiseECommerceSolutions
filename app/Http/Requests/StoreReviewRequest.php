<?php

namespace App\Http\Requests;

use App\Dto\ReviewDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:reviews,id',
        ];
    }

    public function toDto(): ReviewDto
    {
        /** @var array<string, int|string|null> $data */
        $data = $this->validated();

        /** @var int $productId */
        $productId = (int) ($data['product_id'] ?? 0);
        /** @var int|null $parentId */
        $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
        /** @var int|null $rating */
        $rating = isset($data['rating']) ? (int) $data['rating'] : null;
        /** @var string $comment */
        $comment = (string) ($data['comment'] ?? '');

        return new ReviewDto(
            productId: $productId,
            parentId: $parentId,
            rating: $rating,
            comment: $comment,
        );
    }
}
