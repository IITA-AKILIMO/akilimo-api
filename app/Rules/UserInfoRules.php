<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Rules;

class UserInfoRules
{
    public static function rules(): array
    {

        return [
            'user_info' => ['required', 'array'],
            'user_info.device_token' => ['required', 'uuid'],
            'user_info.risk_attitude' => ['required', 'numeric'],
            'user_info.phone_number' => ['nullable', 'string'],
            'user_info.user_name' => ['required', 'string', 'max:255'],
            'user_info.first_name' => ['required', 'string', 'max:255'],
            'user_info.last_name' => ['required', 'string', 'max:255'],
            'user_info.gender' => ['required', 'in:M,F'],
            'user_info.email_address' => ['nullable', 'email'],
            'user_info.farm_name' => ['nullable', 'string', 'max:255'],
            'user_info.send_sms' => ['boolean'],
            'user_info.send_email' => ['boolean'],
        ];

    }

    public static function messages(): array
    {
        return [
            'user_info.device_token.uuid' => 'Device token must be a valid UUID.',
        ];
    }
}
