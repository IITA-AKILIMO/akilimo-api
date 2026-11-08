<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FertilizerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];

        return [
            'name' => [...$required, 'string', 'max:255'],
            'type' => [...$required, 'string', 'max:50'],
            'fertilizer_key' => [...$required, 'string', 'max:50'],
            'fertilizer_label' => ['nullable', 'string', 'max:50'],
            'weight' => [...$required, 'integer', 'min:1'],
            'country' => [...$required, 'string', 'size:2'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'use_case' => [...$required, 'string', 'max:50'],
            'cis' => ['boolean'],
            'cim' => ['boolean'],
            'available' => ['boolean'],
        ];
    }
}
