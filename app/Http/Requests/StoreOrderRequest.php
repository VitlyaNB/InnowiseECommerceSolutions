<?php

namespace App\Http\Requests;

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
}
