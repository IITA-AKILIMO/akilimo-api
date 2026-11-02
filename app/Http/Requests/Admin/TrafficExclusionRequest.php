<?php

namespace App\Http\Requests\Admin;

use App\Models\TrafficExclusion;
use App\Service\TrafficExclusionService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrafficExclusionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('value'))) {
            $this->merge(['value' => trim($this->input('value'))]);
        }
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];

        return [
            'field' => [...$required, Rule::in(array_keys(TrafficExclusionService::FIELDS))],
            'match_type' => [...$required, Rule::in(array_keys(TrafficExclusionService::MATCH_TYPES))],
            'value' => [...$required, 'string', 'max:191', $this->valueRule()],
            'note' => ['nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    /** A pattern must be one MariaDB accepts; a phone rule needs digits unless it is a pattern. */
    private function valueRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            // On an update that leaves them out, the saved rule's field and match type apply
            $saved = $this->route('id') ? TrafficExclusion::find($this->route('id')) : null;
            $field = $this->input('field', $saved?->field);
            $match = $this->input('match_type', $saved?->match_type);

            if ($match === 'regex' && ! TrafficExclusionService::isValidPattern((string) $value)) {
                $fail('This is not a valid pattern.');
            } elseif ($field === 'phone' && $match !== 'regex' && ! preg_match('/\d/', (string) $value)) {
                $fail('A phone number rule needs at least one digit.');
            }
        };
    }
}
