<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Middleware CheckAdmin уже проверяет права, здесь просто разрешаем
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $this->route('id'),
            'role' => 'sometimes|string|in:user,admin', // Ограничиваем список ролей
        ];
    }
}
