<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return Auth::check() && $user !== null && $user->role === 'admin';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}
