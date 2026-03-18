<?php

namespace App\Http\Requests;

use App\Dto\CategoryDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:categories'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function toDto(): CategoryDto
    {
        return new CategoryDto(
            name: $this->validated('name'),
            image: $this->file('image') instanceof UploadedFile ? $this->file('image') : null,
        );
    }
}
