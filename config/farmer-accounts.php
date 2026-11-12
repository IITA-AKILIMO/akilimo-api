<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

return [

    /** Version label of the consent wording the farmer agreed to (stored in users.consent_version). The final wording comes from the owner. */
    'consent_version' => env('FARMER_CONSENT_VERSION', 'draft-1'),

    /**
     * Whether verification and reset emails are sent. Null means: only when a real mailer is set (MAIL_MAILER is not "log"),
     * or outside production. Until a hosted email service is configured, production keeps this off.
     */
    'mail_enabled' => env('FARMER_MAIL_ENABLED') === null ? null : filter_var(env('FARMER_MAIL_ENABLED'), FILTER_VALIDATE_BOOL),

    /** Minutes a password reset code stays valid */
    'reset_ttl_minutes' => (int)env('FARMER_RESET_TTL_MINUTES', 60),

    /** Minutes an email verification code stays valid */
    'verify_ttl_minutes' => (int)env('FARMER_VERIFY_TTL_MINUTES', 1440),

    /** Reject passwords found in known breaches (calls the Have I Been Pwned range API; off by default) */
    'check_breached_passwords' => filter_var(env('FARMER_CHECK_BREACHED_PASSWORDS', false), FILTER_VALIDATE_BOOL),

    /**
     * Social sign-in providers that are switched on, comma separated (names of Socialite drivers, e.g. "google,facebook").
     * Empty keeps social sign-in off. Another provider needs its driver (Socialite, or a SocialiteProviders package) and a services.php entry.
     */
    'social_providers' => array_values(array_filter(array_map('trim', explode(',', (string)env('FARMER_SOCIAL_PROVIDERS', ''))))),

    /**
     * Non-secret OAuth client IDs the token must have been issued to, per provider (comma separated in .env).
     * Socialite itself does not check which app a token was issued for, so the API does (Google: tokeninfo).
     */
    'client_ids' => [
        'google' => array_values(array_filter(array_map('trim', explode(',', (string)env('GOOGLE_CLIENT_ID', ''))))),
    ],
];
