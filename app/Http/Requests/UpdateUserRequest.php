<?php

namespace App\Http\Requests;

use App\Dto\UpdateUserDto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . (is_scalar($userId) ? $userId : ''),
            'role' => 'sometimes|string|in:user,admin',
            'balance' => 'sometimes|numeric|min:0',
        ];
    }

    public function toDto(): UpdateUserDto
    {
        return new UpdateUserDto(
            name: $this->validated('name'),
            email: $this->validated('email'),
            role: $this->validated('role'),
            balance: $this->has('balance') ? (float) $this->validated('balance') : null,
        );
    }
}
