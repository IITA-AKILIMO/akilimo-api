<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Requests\Admin;

use App\Enums\EnumMessageType;
use App\Repositories\PushDeviceRepo;
use App\Repositories\UserRepo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // A recipient id may arrive as a number (an account id); the rules and lookups expect text
        if (is_array($this->recipients)) {
            $this->merge(['recipients' => array_map(
                fn ($r) => is_array($r) && isset($r['id']) && is_int($r['id']) ? [...$r, 'id' => (string)$r['id']] : $r,
                $this->recipients,
            )]);
        }

        // Topics and the app's filters are upper case for countries and lower case for languages
        $this->merge(array_filter([
            'country' => is_string($this->country) ? strtoupper(trim($this->country)) : null,
            'language' => is_string($this->language) ? strtolower(trim($this->language)) : null,
        ], fn ($v) => $v !== null && $v !== ''));
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(EnumMessageType::class)],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
            // Audience: either, both or neither (neither is stored for the sync only: there is no topic for everyone)
            'country' => ['nullable', 'string', 'size:2', 'regex:/^[A-Z]{2}$/', 'exists:countries,code'],
            'language' => ['nullable', 'string', 'regex:/^[a-z]{2,3}$/'],
            'target' => ['nullable', Rule::in(EnumMessageType::TARGETS)],
            'expires_at' => ['nullable', 'date', 'after:now'],
            // Private message: one per recipient, a farmer account (every phone signed in to it) or one phone
            'recipients' => ['nullable', 'array', 'max:100', 'prohibits:country,language'],
            'recipients.*.type' => ['required', Rule::in(['user', 'device'])],
            'recipients.*.id' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach ((array)$this->input('recipients', []) as $i => $recipient) {
                if (!is_array($recipient) || !isset($recipient['type'], $recipient['id']) || !is_scalar($recipient['id'])) {
                    continue; // the field rules report it
                }
                $found = match ($recipient['type']) {
                    'user' => app(UserRepo::class)->isFarmer((string)$recipient['id']),
                    'device' => app(PushDeviceRepo::class)->hasDeviceToken((string)$recipient['id']),
                    default => true,
                };
                if (!$found) {
                    $validator->errors()->add("recipients.{$i}.id", 'This ' . $recipient['type'] . ' was not found.');
                }
            }
        }];
    }
}
