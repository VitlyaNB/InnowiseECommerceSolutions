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
            'parent_id' => 'nullable|exists:reviews,id'
        ];
    }

    public function toDto(): ReviewDto
    {
        return new ReviewDto(
            productId: (int) $this->validated('product_id'),
            parentId: $this->has('parent_id') ? (int) $this->validated('parent_id') : null,
            rating: $this->has('rating') ? (int) $this->validated('rating') : null,
            comment: (string) $this->validated('comment'),
        );
    }
}
