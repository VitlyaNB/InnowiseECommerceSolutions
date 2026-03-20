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
            'email' => 'sometimes|email|unique:users,email,'.(is_scalar($userId) ? $userId : ''),
            'role' => 'sometimes|string|in:user,admin',
            'balance' => 'sometimes|numeric|min:0',
        ];
    }

    public function toDto(): UpdateUserDto
    {
        /** @var array<string, int|float|string|null> $data */
        $data = $this->validated();

        /** @var string|null $name */
        $name = $data['name'] ?? null;
        /** @var string|null $email */
        $email = $data['email'] ?? null;
        /** @var string|null $role */
        $role = $data['role'] ?? null;
        /** @var float|null $balance */
        $balance = isset($data['balance']) ? (float) $data['balance'] : null;

        return new UpdateUserDto(
            name: $name,
            email: $email,
            role: $role,
            balance: $balance,
        );
    }
}
