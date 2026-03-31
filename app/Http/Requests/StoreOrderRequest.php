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
            'selected_item_ids' => 'required|array|min:1',
            'selected_item_ids.*' => 'exists:cart_items,id',
        ];
    }

    public function toDto(): OrderDto
    {
        /** @var array<string, string|array<int, int|string>|null> $data */
        $data = $this->validated();

        /** @var string $shippingAddress */
        $shippingAddress = isset($data['shipping_address']) && is_string($data['shipping_address'])
            ? $data['shipping_address']
            : '';
        /** @var array<int, int> $selectedItemIds */
        $selectedItemIds = [];
        if (isset($data['selected_item_ids']) && is_array($data['selected_item_ids'])) {
            foreach ($data['selected_item_ids'] as $id) {
                $selectedItemIds[] = (int) $id;
            }
        }

        return new OrderDto(
            selectedItemIds: $selectedItemIds,
            shippingAddress: $shippingAddress,
        );
    }
}
