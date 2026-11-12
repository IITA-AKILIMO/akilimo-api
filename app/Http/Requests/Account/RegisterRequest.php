<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Account;

class RegisterRequest extends AccountRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => $this->passwordRules(),
            'name' => ['nullable', 'string', 'max:255'],
            'consent' => ['required', 'accepted'],
            'device_token' => ['nullable', 'string', 'max:255'],
        ];
    }
}
