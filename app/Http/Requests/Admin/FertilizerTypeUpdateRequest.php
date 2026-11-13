<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Changing a fertiliser: its name, usual bag weight and category. The code and the
 * catalogue label are fixed, because prices and keys are built from them.
 */
class FertilizerTypeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:1', 'max:1000'],
            'category' => ['required', 'string', Rule::exists('fertilizer_categories', 'value')],
        ];
    }
}
