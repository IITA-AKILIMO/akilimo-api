<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AppVersionFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_supported' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'latest' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'message' => ['nullable', 'string', 'max:255'],
            'store_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
