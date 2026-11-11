<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * A fertiliser sold in a country. The fertiliser itself (name, label, bag weight)
 * comes from its type in fertilizer_types and the key from the type and the
 * country, so they are not accepted here: pick the type, the country and how the
 * country uses it.
 */
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
            'type' => [...$required, 'string', Rule::exists('fertilizer_types', 'type')],
            'country' => [...$required, 'string', 'size:2'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            // Picked from fertilizer_use_cases
            'use_case' => [...$required, 'string', Rule::exists('fertilizer_use_cases', 'value')],
            // The fertiliser's own category (shared by every country that sells it), picked from fertilizer_categories
            'category' => ['sometimes', 'string', Rule::exists('fertilizer_categories', 'value')],
            'cis' => ['boolean'],
            'cim' => ['boolean'],
            'available' => ['boolean'],
        ];
    }

    /**
     * The type is saved with the spelling in fertilizer_types (the database
     * matches without regard to case).
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (isset($data['type'])) {
            $data['type'] = (string) (DB::table('fertilizer_types')->where('type', $data['type'])->value('type') ?? $data['type']);
        }

        if (isset($data['use_case'])) {
            $data['use_case'] = (string) (DB::table('fertilizer_use_cases')->where('value', $data['use_case'])->value('value') ?? $data['use_case']);
        }

        return $key === null ? $data : data_get($data, $key, $default);
    }
}
