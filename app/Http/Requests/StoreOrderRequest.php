<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['integer', 'exists:cart_items,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Выберите товары для оплаты.',
            'items.min' => 'Выберите хотя бы один товар.',
        ];
    }
}
