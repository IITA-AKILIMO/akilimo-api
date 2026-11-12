<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Enums\EnumUserRole;
use App\Models\User;
use App\Models\UserIdentity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Socialite;
use Throwable;

/**
 * Social sign-in for farmer accounts, on laravel/socialite: the app signs the farmer in with the provider (Google,
 * Facebook, ...) and sends the provider's access token; Socialite reads the profile from that token.
 *
 * Socialite does not check which app a token was issued to, so a token that some other app obtained could be replayed here.
 * assertIssuedToUs() closes that for Google; add a case for each further provider that offers such a check.
 */
class SocialSignInService
{
    public const FAILED = 'failed';

    public const CONSENT_REQUIRED = 'consent_required';

    public const NO_EMAIL = 'no_email';

    public const EMAIL_UNVERIFIED = 'email_unverified';

    public function __construct(private readonly FarmerAccountService $accounts) {}

    public function enabled(string $provider): bool
    {
        return in_array($provider, config('farmer-accounts.social_providers', []), true);
    }

    /**
     * @return array{user: User, token: string, expires_at: Carbon, claimed: int}|string a sign-in, or one of the error constants
     */
    public function signIn(string $provider, string $accessToken, bool $consent, ?string $deviceToken): array|string
    {
        $profile = $this->profile($provider, $accessToken);

        if ($profile === null) {
            return self::FAILED;
        }

        $subject = (string)$profile->getId();
        $email = $profile->getEmail() ? strtolower($profile->getEmail()) : null;
        $emailVerified = $this->emailVerified($profile);

        $identity = UserIdentity::where('provider', $provider)->where('subject', $subject)->first();
        $user = $identity?->user;

        if ($user === null && $email !== null && $emailVerified) {
            // Link to an existing account only when the provider vouches for the address
            $user = User::where('email', $email)->first();
        }

        if ($user === null) {
            if ($email === null) {
                return self::NO_EMAIL;
            }
            if (!$consent) {
                return self::CONSENT_REQUIRED;
            }
            if (User::where('email', $email)->exists()) {
                // An unverified address that belongs to an account must not be taken over
                return self::EMAIL_UNVERIFIED;
            }
        }

        $user = DB::transaction(function () use ($user, $provider, $subject, $email, $emailVerified, $consent, $profile) {
            $user ??= User::create([
                'name' => (string)($profile->getName() ?? ''),
                'username' => 'farmer_' . strtolower(str()->random(10)),
                'email' => $email,
                'password' => null, // a social-only account has no password; password sign-in refuses it
                'role' => EnumUserRole::User,
            ]);

            UserIdentity::firstOrCreate(['provider' => $provider, 'subject' => $subject], ['user_id' => $user->id]);

            if ($emailVerified && $user->email_verified_at === null && strtolower($user->email) === $email) {
                $user->email_verified_at = now();
            }

            if ($consent && $user->consent_at === null) {
                $user->consent_at = now();
                $user->consent_version = config('farmer-accounts.consent_version');
            }

            $user->save();

            return $user;
        });

        return $this->accounts->signIn($user, $deviceToken);
    }

    private function profile(string $provider, string $accessToken): ?SocialUser
    {
        try {
            if (!$this->assertIssuedToUs($provider, $accessToken)) {
                return null;
            }

            return Socialite::driver($provider)->stateless()->userFromToken($accessToken);
        } catch (Throwable) {
            return null; // a bad, expired or foreign token: the caller answers generically
        }
    }

    /** True when the token was issued to one of our own OAuth clients (or the provider has no such check configured). */
    private function assertIssuedToUs(string $provider, string $accessToken): bool
    {
        if ($provider !== 'google') {
            return true;
        }

        $clientIds = config('farmer-accounts.client_ids.google', []);

        if ($clientIds === []) {
            return false; // Google sign-in without client IDs would accept any app's token
        }

        $info = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', ['access_token' => $accessToken]);

        if (!$info->ok()) {
            return false;
        }

        return in_array($info->json('aud'), $clientIds, true) || in_array($info->json('azp'), $clientIds, true);
    }

    private function emailVerified(SocialUser $profile): bool
    {
        $raw = $profile->user ?? $profile->getRaw() ?? [];
        $flag = $raw['verified_email'] ?? $raw['email_verified'] ?? false;

        return $flag === true || $flag === 'true' || $flag === 1;
    }
}
