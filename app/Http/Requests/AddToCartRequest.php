<?php

namespace App\Http\Requests;

use App\Dto\CartItemDto;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
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
            'quantity' => 'nullable|integer|min:1',
        ];
    }

    public function toDto(): CartItemDto
    {
        return new CartItemDto(
            id: 0,
            productId: (int) $this->validated('product_id'),
            quantity: (int) $this->validated('quantity', 1),
        );
    }
}
