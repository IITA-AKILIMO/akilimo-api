<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StarchPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];

        return [
            'starch_factory_id' => [...$required, 'integer', 'exists:starch_factories,id'],
            'price_class' => [...$required, 'integer', 'min:0'],
            'min_starch' => [...$required, 'numeric', 'min:0'],
            'range_starch' => ['nullable', 'string', 'max:50'],
            'price' => [...$required, 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
        ];
    }
}
