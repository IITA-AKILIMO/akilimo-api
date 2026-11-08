<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CaseInsensitiveIn implements Rule
{
    protected array $allowed;

    protected string $inputValue;

    public function __construct(array $allowed)
    {
        // normalize allowed values to lowercase
        $this->allowed = array_map('strtolower', $allowed);
    }

    public function passes($attribute, $value): bool
    {
        $this->inputValue = $value;

        return in_array(strtolower($value), $this->allowed, true);
    }

    public function message(): string
    {
        return 'The :attribute value "' . $this->inputValue .
            '" is invalid. Allowed values are: ' . implode(', ', $this->allowed) . '.';
    }
}
