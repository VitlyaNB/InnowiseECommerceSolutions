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
        /** @var array<string, int|string> $data */
        $data = $this->validated();

        return new CartItemDto(
            id: 0,
            productId: (int) $data['product_id'],
            quantity: (int) ($data['quantity'] ?? 1),
        );
    }
}
