<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Account;

class ConsentRequest extends AccountRequest
{
    public function rules(): array
    {
        return [
            'consent' => ['required', 'accepted'],
            'device_token' => ['nullable', 'string', 'max:255'],
        ];
    }
}
