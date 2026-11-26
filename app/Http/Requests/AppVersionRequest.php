<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests;

use App\Service\AppVersionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => ['nullable', 'string', Rule::in(AppVersionService::PLATFORMS)],
        ];
    }
}
