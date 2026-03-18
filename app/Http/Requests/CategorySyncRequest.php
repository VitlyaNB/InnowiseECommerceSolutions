<?php

namespace App\Http\Requests;

use App\Dto\ExternalCategorySyncCommandDto;
use Illuminate\Foundation\Http\FormRequest;

class CategorySyncRequest extends FormRequest
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
            'async' => ['nullable', 'boolean'],
        ];
    }

    public function toDto(): ExternalCategorySyncCommandDto
    {
        return new ExternalCategorySyncCommandDto(
            async: (bool) $this->boolean('async'),
        );
    }
}
