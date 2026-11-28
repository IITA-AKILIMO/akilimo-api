<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Registers or refreshes a phone for push (PUT /v1/devices). */
class DeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_filter([
            'country' => is_string($this->country) ? strtoupper(trim($this->country)) : null,
            'language' => is_string($this->language) ? strtolower(trim($this->language)) : null,
        ], fn ($v) => $v !== null && $v !== ''));
    }

    public function rules(): array
    {
        return [
            // The install id the app sends with account and compute requests
            'device_token' => ['required', 'string', 'max:255'],
            // Firebase registration token
            'fcm_token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'in:android'],
            'app_version' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'regex:/^[A-Z]{2}$/'],
            'language' => ['nullable', 'string', 'regex:/^[a-z]{2,3}$/'],
        ];
    }
}
