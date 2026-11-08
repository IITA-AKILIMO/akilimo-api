<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');
        $isCreate = $this->isMethod('POST');
        $required = $isCreate ? ['required'] : ['sometimes', 'required'];

        return [
            'name' => [...$required, 'string', 'max:255'],
            'username' => [...$required, 'string', 'max:255', 'unique:users,username' . ($userId ? ",{$userId}" : '')],
            'email' => [...$required, 'email', 'max:255', 'unique:users,email' . ($userId ? ",{$userId}" : '')],
            'password' => [$isCreate ? 'required' : 'nullable', 'confirmed', Password::min(8)],
        ];
    }
}
