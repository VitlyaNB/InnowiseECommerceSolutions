<?php

namespace App\Http\Requests;

use App\Dto\RegisterDto;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function toDto(): RegisterDto
    {
        /** @var array<string, string> $data */
        $data = $this->validated();

        return new RegisterDto(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
        );
    }
}
