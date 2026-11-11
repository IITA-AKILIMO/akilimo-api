<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use App\Service\PriceLookupService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DefaultPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];
        $lookups = app(PriceLookupService::class);

        return [
            'country' => [...$required, 'string', 'size:2', 'uppercase', Rule::exists('countries', 'code')],
            'region' => ['nullable', 'string', 'max:50'],
            // Picked from price_items; a new item is added there first
            'item' => [...$required, 'string', 'max:50', $lookups->itemRule()],
            // The fertiliser this per-bag price is for (UREA); with the country it gives the option list key (UREA_TZ)
            'fertilizer_type' => ['nullable', 'string', 'max:50', Rule::exists('fertilizer_types', 'type')],
            'price' => [...$required, 'numeric', 'min:0'],
            'subsidised_price' => ['nullable', 'numeric', 'min:0'],
            // Picked from lists in the form, so a typo cannot reach a price list
            'unit' => ['nullable', 'string', $lookups->unitRule()],
            'bag_kg' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'currency' => ['nullable', 'string', 'size:3', 'uppercase', $lookups->currencyRule()],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'source' => ['nullable', 'string', 'max:255'],
            'source_date' => ['nullable', 'date'],
            'confidence' => ['nullable', Rule::in(config('price_catalogue.confidence_levels'))],
            'effective_from' => ['nullable', 'date'],
        ];
    }

    /**
     * The allowed range must contain the prices it describes.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $min = $this->input('min_price');
            $max = $this->input('max_price');

            if ($min === null || $max === null) {
                return;
            }

            foreach (['price', 'subsidised_price'] as $field) {
                $value = $this->input($field);
                if ($value !== null && ($value < $min || $value > $max)) {
                    $validator->errors()->add(
                        $field,
                        'The ' . str_replace('_', ' ', $field) . ' must be between min_price and max_price.',
                    );
                }
            }
        });
    }

    /**
     * The item is saved as the list spells it (cassUP, not cassup).
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (isset($data['item'])) {
            $data['item'] = app(PriceLookupService::class)->canonicalItem($data['item']);
        }

        return $key === null ? $data : data_get($data, $key, $default);
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('currency'))) {
            $this->merge(['currency' => strtoupper($this->input('currency'))]);
        }
    }
}
