<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];

        return [
            'key' => [...$required, 'string', 'max:255'],
            'en' => [...$required, 'string'],
            'sw' => ['nullable', 'string'],
            'rw' => ['nullable', 'string'],
        ];
    }
}
