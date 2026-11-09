<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FertilizerPriceRequest extends FormRequest
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
            'fertilizer_key' => [...$required, 'string', 'max:50'],
            'min_price' => [...$required, 'numeric', 'min:0'],
            'max_price' => [...$required, 'numeric', 'min:0', 'gte:min_price'],
            'price_per_bag' => [...$required, 'numeric', 'min:0'],
            'price_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'desc' => ['nullable', 'string', 'max:255'],
        ];
    }
}
