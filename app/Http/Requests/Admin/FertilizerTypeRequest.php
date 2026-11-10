<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use App\Service\FertilizerTypeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * A new fertiliser: its name, bag weight and optionally the label the catalogue
 * uses. The type code is generated, never entered.
 */
class FertilizerTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:1', 'max:1000'],
            'label' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_+]+$/', Rule::unique('fertilizer_types', 'label')],
        ];
    }

    public function messages(): array
    {
        return [
            'label.regex' => 'The label may only contain letters, digits, underscores and +.',
            'label.unique' => 'A fertilizer already uses this label.',
        ];
    }

    /**
     * The name must make a code, and the name must not already be a fertilizer.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $name = (string) $this->input('name');

            if ($name !== '' && app(FertilizerTypeService::class)->codeFor($name) === '') {
                $validator->errors()->add('name', 'The name must contain letters or digits.');
            }
        });
    }
}
