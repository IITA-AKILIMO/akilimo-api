<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use App\Enums\EnumMessageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Topics and the app's filters are upper case for countries and lower case for languages
        $this->merge(array_filter([
            'country' => is_string($this->country) ? strtoupper(trim($this->country)) : null,
            'language' => is_string($this->language) ? strtolower(trim($this->language)) : null,
        ], fn ($v) => $v !== null && $v !== ''));
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(EnumMessageType::class)],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
            // Audience: either, both or neither (neither is stored for the sync only: there is no topic for everyone)
            'country' => ['nullable', 'string', 'size:2', 'regex:/^[A-Z]{2}$/', 'exists:countries,code'],
            'language' => ['nullable', 'string', 'regex:/^[a-z]{2,3}$/'],
            'target' => ['nullable', Rule::in(EnumMessageType::TARGETS)],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
