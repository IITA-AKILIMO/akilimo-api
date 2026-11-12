<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    // Social sign-in for farmer accounts (laravel/socialite, token flow: the app sends the provider's access token).
    // No secret is needed to read a profile from a token, so none is stored; redirect is only there because Socialite requires the key.
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => null,
        'redirect' => '/',
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => null,
        'redirect' => '/',
    ],

    // More sign-in providers for farmer accounts. To switch one on: uncomment its block, set the variable(s) in .env,
    // add the provider to FARMER_SOCIAL_PROVIDERS, and (outside Socialite's built-in list) install its SocialiteProviders package.
    // The app then signs in with the provider and posts the access token to POST /v1/account/social/{provider}.
    // Socialite does not check which app a token was issued to: add an audience check for the provider in
    // SocialSignInService::assertIssuedToUs() before relying on it (see docs/FARMER_ACCOUNTS_API.md).

    // 'github' => [
    //     'client_id' => env('GITHUB_CLIENT_ID'),
    //     'client_secret' => null,
    //     'redirect' => '/',
    // ],

    // 'gitlab' => [
    //     'client_id' => env('GITLAB_CLIENT_ID'),
    //     'client_secret' => null,
    //     'redirect' => '/',
    // ],

    // 'bitbucket' => [
    //     'client_id' => env('BITBUCKET_CLIENT_ID'),
    //     'client_secret' => null,
    //     'redirect' => '/',
    // ],

    // 'linkedin-openid' => [
    //     'client_id' => env('LINKEDIN_CLIENT_ID'),
    //     'client_secret' => null,
    //     'redirect' => '/',
    // ],

    // 'x' => [
    //     'client_id' => env('X_CLIENT_ID'),
    //     'client_secret' => null,
    //     'redirect' => '/',
    // ],

    // 'slack-openid' => [
    //     'client_id' => env('SLACK_CLIENT_ID'),
    //     'client_secret' => null,
    //     'redirect' => '/',
    // ],

    // Apple and Microsoft are not built into Socialite: install socialiteproviders/apple or socialiteproviders/microsoft
    // and register the driver in a service provider (see https://socialiteproviders.com).
    // 'apple' => [
    //     'client_id' => env('APPLE_CLIENT_ID'),
    //     'client_secret' => null,
    //     'redirect' => '/',
    // ],

    // 'microsoft' => [
    //     'client_id' => env('MICROSOFT_CLIENT_ID'),
    //     'client_secret' => null,
    //     'redirect' => '/',
    // ],

    'slack' => ['notifications' => [
        'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
        'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
    ],
    ],

];
