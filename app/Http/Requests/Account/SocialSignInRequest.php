<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Account;

class SocialSignInRequest extends AccountRequest
{
    public function rules(): array
    {
        return [
            'access_token' => ['required', 'string', 'max:4096'],
            'consent' => ['nullable', 'boolean'],
            'device_token' => ['nullable', 'string', 'max:255'],
        ];
    }
}
