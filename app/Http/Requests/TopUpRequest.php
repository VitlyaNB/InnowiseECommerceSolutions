<?php

namespace App\Http\Requests;

use App\Dto\TopUpDto;
use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function toDto(): TopUpDto
    {
        return new TopUpDto(
            amount: (float) $this->validated('amount'),
        );
    }
}
