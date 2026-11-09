<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use App\Enums\EnumUserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');
        $isCreate = $this->isMethod('POST');
        $required = $isCreate ? ['required'] : ['sometimes', 'required'];

        return [
            'name' => [...$required, 'string', 'max:255'],
            'username' => [...$required, 'string', 'max:255', 'unique:users,username' . ($userId ? ",{$userId}" : '')],
            'email' => [...$required, 'email', 'max:255', 'unique:users,email' . ($userId ? ",{$userId}" : '')],
            'password' => [$isCreate ? 'required' : 'nullable', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::enum(EnumUserRole::class)],
            'reviewer_countries' => ['nullable', 'array'],
            'reviewer_countries.*' => ['string', 'size:2', 'exists:countries,code'],
        ];
    }
}
