<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use App\Enums\PriceBasis;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];

        return [
            'code' => [...$required, 'string', 'size:2', 'uppercase'],
            'name' => [...$required, 'string', 'max:100'],
            'active' => ['boolean'],
            'default_price_basis' => ['sometimes', 'required', Rule::in(PriceBasis::values())],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
