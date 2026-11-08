<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CassavaPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];

        return [
            'country' => [...$required, 'string', 'size:2'],
            'min_local_price' => [...$required, 'numeric', 'min:0'],
            'max_local_price' => [...$required, 'numeric', 'min:0', 'gte:min_local_price'],
            'min_usd' => [...$required, 'numeric', 'min:0'],
            'max_usd' => [...$required, 'numeric', 'min:0', 'gte:min_usd'],
            'min_price' => [...$required, 'numeric', 'min:0'],
            'max_price' => [...$required, 'numeric', 'min:0', 'gte:min_price'],
            'price_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
