<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];

        return [
            'country_code' => [...$required, 'string', 'size:2'],
            'country' => [...$required, 'string', 'max:100'],
            'currency_name' => [...$required, 'string', 'max:100'],
            'currency_code' => [...$required, 'string', 'max:10'],
            'currency_symbol' => [...$required, 'string', 'max:10'],
            'currency_native_symbol' => ['nullable', 'string', 'max:10'],
            'name_plural' => ['nullable', 'string', 'max:100'],
        ];
    }
}
