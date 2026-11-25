<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use App\Service\AppVersionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppVersionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => ['nullable', 'string', Rule::in(AppVersionService::PLATFORMS)],
            'min_supported' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'latest' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'message' => ['nullable', 'string', 'max:255'],
            'store_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
