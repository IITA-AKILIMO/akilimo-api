<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service\Fcm;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Sends data-only messages to FCM topics with the HTTP v1 API. No device tokens are involved: the app subscribes itself to
 * `country_<CODE>` and `lang_<code>` (see docs/MESSAGES_API.md). The access token comes from a service account: a signed
 * JWT exchanged at Google's token endpoint, cached until shortly before it expires. Needs ext-openssl, no extra package.
 */
class FcmClient
{
    private const TOKEN_CACHE_KEY = 'fcm.access_token';

    private const TOKEN_TTL_SECONDS = 3300;

    public function isConfigured(): bool
    {
        return config('fcm.enabled') && config('fcm.project_id') && $this->credentials() !== null;
    }

    /**
     * The FCM target for an audience: one topic, or a condition when both a country and a language are given; null when
     * neither is (there is no topic for "everyone", so such a message reaches phones through the sync only).
     *
     * @return array{topic: string}|array{condition: string}|null
     */
    public static function target(?string $country, ?string $language): ?array
    {
        $topics = array_values(array_filter([
            $country ? 'country_' . strtoupper($country) : null,
            $language ? 'lang_' . strtolower($language) : null,
        ]));

        return match (count($topics)) {
            0 => null,
            1 => ['topic' => $topics[0]],
            default => ['condition' => implode(' && ', array_map(fn (string $t) => "'{$t}' in topics", $topics))],
        };
    }

    /**
     * Sends one data message. Every value must be a string (an FCM rule); nulls are dropped.
     *
     * @param array{topic: string}|array{condition: string} $target
     * @param array<string, string|null> $data
     *
     * @throws RuntimeException when FCM refuses the message or the access token cannot be obtained
     */
    public function send(array $target, array $data): void
    {
        $response = Http::withToken($this->accessToken())
            ->timeout((int)config('fcm.timeout'))
            ->acceptJson()
            ->post(
                'https://fcm.googleapis.com/v1/projects/' . config('fcm.project_id') . '/messages:send',
                ['message' => [...$target, 'data' => array_map('strval', array_filter($data, fn ($v) => $v !== null)), 'android' => ['priority' => 'HIGH']]],
            );

        if ($response->failed()) {
            throw new RuntimeException('FCM send failed (' . $response->status() . '): ' . $response->body());
        }
    }

    private function accessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_TTL_SECONDS, function (): string {
            $account = $this->credentials() ?? throw new RuntimeException('FCM credentials are not configured.');
            $now = time();
            $tokenUri = $account['token_uri'] ?? 'https://oauth2.googleapis.com/token';
            $unsigned = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR)) . '.' .
                $this->base64Url(json_encode([
                    'iss' => $account['client_email'],
                    'scope' => config('fcm.scope'),
                    'aud' => $tokenUri,
                    'iat' => $now,
                    'exp' => $now + 3600,
                ], JSON_THROW_ON_ERROR));

            if (!openssl_sign($unsigned, $signature, $account['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('FCM: the service account key could not sign the token request.');
            }

            $response = Http::asForm()->timeout((int)config('fcm.timeout'))->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsigned . '.' . $this->base64Url($signature),
            ]);

            if ($response->failed() || !$response->json('access_token')) {
                throw new RuntimeException('FCM: no access token (' . $response->status() . ').');
            }

            return (string)$response->json('access_token');
        });
    }

    /** @return array{client_email: string, private_key: string, token_uri?: string}|null */
    private function credentials(): ?array
    {
        $value = config('fcm.credentials');
        if (!is_string($value) || $value === '') {
            return null;
        }
        $json = str_starts_with(ltrim($value), '{') ? $value : (is_file($value) ? file_get_contents($value) : false);
        $account = $json ? json_decode($json, true) : null;

        return is_array($account) && isset($account['client_email'], $account['private_key']) ? $account : null;
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
