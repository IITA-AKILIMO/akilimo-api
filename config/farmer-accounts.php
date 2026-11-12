<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

/*
|--------------------------------------------------------------------------
| Farmer accounts
|--------------------------------------------------------------------------
|
| Settings for the optional farmer accounts (docs/FARMER_ACCOUNTS_API.md). Provider credentials (client ID, secret,
| redirect URI) live in config/services.php; this file only holds the account behaviour. Every value is set by an
| environment variable, and the defaults keep the risky parts off.
|
| Email: verification and reset codes are sent through the Laravel mailer (MAIL_MAILER). In production they are only sent
| once a real mailer is set (anything but "log"), so they stay off until the hosted email service is configured.
|
*/

/** A comma separated .env value as a list: "a, b" becomes ['a', 'b'], empty becomes []. */
$list = fn (string $name): array => array_values(array_filter(array_map('trim', explode(',', (string)env($name, '')))));

return [

    // ── Consent ───────────────────────────────────────────────────────────────

    /** Label of the consent wording the farmer agreed to, stored in users.consent_version. The final wording comes from the owner. */
    'consent_version' => env('FARMER_CONSENT_VERSION', 'draft-1'),

    // ── Email codes ───────────────────────────────────────────────────────────

    /** Minutes a password reset code stays valid. */
    'reset_ttl_minutes' => (int)env('FARMER_RESET_TTL_MINUTES', 60),

    /** Minutes an email verification code stays valid. */
    'verify_ttl_minutes' => (int)env('FARMER_VERIFY_TTL_MINUTES', 1440),

    // ── Passwords ─────────────────────────────────────────────────────────────

    /** Reject passwords found in known breaches (calls the Have I Been Pwned range API). Off by default. */
    'check_breached_passwords' => filter_var(env('FARMER_CHECK_BREACHED_PASSWORDS', false), FILTER_VALIDATE_BOOL),

    // ── Social sign-in (laravel/socialite) ────────────────────────────────────

    /**
     * Providers switched on, as Socialite driver names (e.g. "google,facebook"). Empty keeps social sign-in off.
     * A provider also needs its entry in config/services.php, and a driver (built into Socialite, or a SocialiteProviders package).
     */
    'social_providers' => $list('FARMER_SOCIAL_PROVIDERS'),

    /**
     * OAuth client IDs a provider's token must have been issued to (several allowed: Android and web). Socialite does not
     * check this itself, so SocialSignInService does, for the providers listed here. Google is checked through its tokeninfo
     * endpoint and is refused while this list is empty.
     */
    'client_ids' => [
        'google' => $list('GOOGLE_CLIENT_ID'),
    ],
];
