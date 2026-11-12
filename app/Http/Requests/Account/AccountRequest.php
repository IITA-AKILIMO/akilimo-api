<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/** Shared rules for the farmer account requests. */
abstract class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<int, mixed> */
    protected function passwordRules(): array
    {
        $rule = Password::min(8);

        if (config('farmer-accounts.check_breached_passwords')) {
            $rule = $rule->uncompromised();
        }

        return ['required', 'string', $rule];
    }
}
