<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OperationCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];

        return [
            'operation_name' => [...$required, 'string', 'max:100'],
            'operation_type' => [...$required, 'string', 'max:50'],
            'country_code' => [...$required, 'string', 'size:2'],
            'min_cost' => [...$required, 'numeric', 'min:0'],
            'max_cost' => [...$required, 'numeric', 'min:0', 'gte:min_cost'],
            'is_active' => ['boolean'],
        ];
    }
}
