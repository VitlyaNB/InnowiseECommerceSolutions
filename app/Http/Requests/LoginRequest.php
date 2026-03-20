<?php

namespace App\Http\Requests;

use App\Dto\LoginDto;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function toDto(): LoginDto
    {
        /** @var array<string, string> $data */
        $data = $this->validated();

        return new LoginDto(
            email: $data['email'],
            password: $data['password'],
        );
    }
}
