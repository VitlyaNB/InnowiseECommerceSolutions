<?php

namespace App\Http\Requests;

use App\Dto\OrderDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'shipping_address' => 'required|string',
            'selected_item_ids' => 'required|array',
            'selected_item_ids.*' => 'exists:cart_items,id',
        ];
    }

    public function toDto(): OrderDto
    {
        /** @var array<int, int> $selectedItemIds */
        $selectedItemIds = $this->validated('selected_item_ids');

        return new OrderDto(
            selectedItemIds: $selectedItemIds,
            shippingAddress: (string) $this->validated('shipping_address'),
        );
    }
}
