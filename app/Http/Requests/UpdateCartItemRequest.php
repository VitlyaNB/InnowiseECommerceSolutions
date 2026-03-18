<?php

namespace App\Http\Requests;

use App\Dto\UpdateCartItemDto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
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
            'quantity' => 'required|integer|min:0',
        ];
    }

    public function toDto(): UpdateCartItemDto
    {
        return new UpdateCartItemDto(
            quantity: (int) $this->validated('quantity'),
        );
    }
}
