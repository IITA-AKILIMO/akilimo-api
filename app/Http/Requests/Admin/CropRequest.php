<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CropRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** The admin form sends the countries as "NG, TZ"; store them as a list of upper-case codes (empty = every country) */
    protected function prepareForValidation(): void
    {
        $countries = $this->input('countries');
        if (is_string($countries)) {
            $list = array_values(array_filter(array_map(fn ($c) => strtoupper(trim($c)), explode(',', $countries))));
            $this->merge(['countries' => $list === [] ? null : $list]);
        }
        if ($this->has('crop_key') && is_string($this->input('crop_key'))) {
            $this->merge(['crop_key' => strtolower(trim($this->input('crop_key')))]);
        }
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];
        $id = $this->route('id');

        return [
            'crop_key' => [...$required, 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('crops', 'crop_key')->ignore($id)],
            'name' => [...$required, 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'advice_available' => ['sometimes', 'boolean'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'size:2'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
