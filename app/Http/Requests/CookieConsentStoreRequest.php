<?php

namespace App\Http\Requests;

use App\Dto\CookieConsentDto;
use Illuminate\Foundation\Http\FormRequest;

class CookieConsentStoreRequest extends FormRequest
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
            'accepted' => ['required', 'boolean'],
        ];
    }

    public function toDto(): CookieConsentDto
    {
        return new CookieConsentDto(
            accepted: (bool) $this->boolean('accepted'),
        );
    }
}
