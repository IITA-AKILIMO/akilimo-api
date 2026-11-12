<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Auth\TokenAbility;
use App\Enums\EnumUserRole;
use App\Http\Middleware\RequireFarmerToken;
use App\Mail\AccountCodeMail;
use App\Models\ApiRequest;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Farmer accounts (docs/FARMER_ACCOUNTS_DESIGN.md): sign-up, sign-in, consent, email confirmation, password reset,
 * and linking a phone's earlier advice to the account. Farmer tokens only ever carry FARMER_ABILITIES, whatever the role.
 * Never log a password, a code, a token or an email address here.
 */
class FarmerAccountService
{
    /**
     * @param array{email: string, password: string, name?: string|null, device_token?: string|null} $data
     * @return array{user: User, token: string, expires_at: Carbon, claimed: int}
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'] ?? '',
            'username' => $this->freshUsername(),
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'role' => EnumUserRole::User,
            'consent_at' => now(),
            'consent_version' => config('farmer-accounts.consent_version'),
        ]);

        $this->sendEmailCode($user, AccountCodeMail::VERIFY);

        return $this->signIn($user, $data['device_token'] ?? null);
    }

    /**
     * Sign in with email and password. Null when the pair is wrong (the caller answers generically).
     *
     * @return array{user: User, token: string, expires_at: Carbon, claimed: int}|null
     */
    public function login(string $email, string $password, ?string $deviceToken): ?array
    {
        $user = User::where('email', strtolower($email))->first();

        if ($user === null || blank($user->password) || !Hash::check($password, $user->password)) {
            return null;
        }

        return $this->signIn($user, $deviceToken);
    }

    /**
     * Issue the farmer token. The phone's earlier advice is claimed only once the farmer has consented.
     *
     * @return array{user: User, token: string, expires_at: Carbon, claimed: int}
     */
    public function signIn(User $user, ?string $deviceToken): array
    {
        $claimed = $this->hasConsented($user) ? $this->claimDevice($user, $deviceToken) : 0;

        $expiresAt = now()->addDays((int)config('auth.token_ttl_days', 30));
        $rawToken = bin2hex(random_bytes(32));

        PersonalAccessToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => RequireFarmerToken::TOKEN_NAME,
            'token' => hash('sha256', $rawToken),
            'abilities' => TokenAbility::FARMER_ABILITIES,
            'expires_at' => $expiresAt,
        ]);

        return ['user' => $user, 'token' => $rawToken, 'expires_at' => $expiresAt, 'claimed' => $claimed];
    }

    public function hasConsented(User $user): bool
    {
        return $user->consent_at !== null;
    }

    /** Record consent for an existing account and claim this phone's earlier advice. Returns the number of requests claimed. */
    public function recordConsent(User $user, ?string $deviceToken): int
    {
        $user->forceFill([
            'consent_at' => now(),
            'consent_version' => config('farmer-accounts.consent_version'),
        ])->save();

        return $this->claimDevice($user, $deviceToken);
    }

    /**
     * Claim the advice this phone saved before the farmer signed in. Only rows with no owner move, so a second
     * claim, or a token another account already claimed, takes nothing.
     */
    public function claimDevice(User $user, ?string $deviceToken): int
    {
        if (blank($deviceToken)) {
            return 0;
        }

        return ApiRequest::query()
            ->where('device_token', $deviceToken)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);
    }

    /** True when verification and reset emails may be sent (see config/farmer-accounts.php). */
    public function mailEnabled(): bool
    {
        return config('farmer-accounts.mail_enabled')
            ?? (config('mail.default') !== 'log' || !app()->isProduction());
    }

    /** Send a fresh confirmation code. False when email is switched off. */
    public function sendVerification(User $user): bool
    {
        return $this->sendEmailCode($user, AccountCodeMail::VERIFY);
    }

    public function verifyEmail(string $email, string $code): bool
    {
        $user = User::where('email', strtolower($email))->first();

        if ($user === null || !$this->consumeCode('verify', $user, $code)) {
            return false;
        }

        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

        return true;
    }

    /** Send a reset code if the address belongs to an account; the caller answers the same either way. */
    public function sendReset(string $email): void
    {
        $user = User::where('email', strtolower($email))->first();

        if ($user !== null) {
            $this->sendEmailCode($user, AccountCodeMail::RESET);
        }
    }

    /** Set a new password with a valid code, and sign the account out everywhere. */
    public function resetPassword(string $email, string $code, string $password): bool
    {
        $user = User::where('email', strtolower($email))->first();

        if ($user === null || !$this->consumeCode('reset', $user, $code)) {
            return false;
        }

        DB::transaction(function () use ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                // The code came to the inbox, so the address is proven
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            $this->revokeAllTokens($user);
        });

        return true;
    }

    /**
     * Delete the account and everything that identifies the farmer. Their past requests stay, for statistics, with the
     * link to the person removed: user_id is set to null and the phone's device_token is cleared on those rows.
     */
    public function deleteAccount(User $user): void
    {
        DB::transaction(function () use ($user) {
            ApiRequest::where('user_id', $user->id)->update(['user_id' => null, 'device_token' => null]);

            $this->revokeAllTokens($user);
            $user->apiKeys()->delete();
            $user->identities()->delete();
            $user->delete();
        });
    }

    public function revokeAllTokens(User $user): void
    {
        PersonalAccessToken::where('tokenable_type', User::class)->where('tokenable_id', $user->id)->delete();
    }

    /** @return array<string, mixed> */
    public function present(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => $user->email_verified_at !== null,
            'consent_required' => !$this->hasConsented($user),
        ];
    }

    private function sendEmailCode(User $user, string $purpose): bool
    {
        if (!$this->mailEnabled()) {
            return false;
        }

        $kind = $purpose === AccountCodeMail::VERIFY ? 'verify' : 'reset';
        $minutes = (int)config($kind === 'verify' ? 'farmer-accounts.verify_ttl_minutes' : 'farmer-accounts.reset_ttl_minutes');
        $code = strtoupper(bin2hex(random_bytes(4)));

        Cache::put($this->codeKey($kind, $user), hash('sha256', $code), now()->addMinutes($minutes));
        Mail::to($user->email)->send(new AccountCodeMail($purpose, $code, $minutes));

        return true;
    }

    /** A code works once: it is removed on the first correct use. */
    private function consumeCode(string $kind, User $user, string $code): bool
    {
        $stored = Cache::get($this->codeKey($kind, $user));

        if (!is_string($stored) || !hash_equals($stored, hash('sha256', strtoupper(trim($code))))) {
            return false;
        }

        Cache::forget($this->codeKey($kind, $user));

        return true;
    }

    private function codeKey(string $kind, User $user): string
    {
        return "account-{$kind}-code:{$user->id}";
    }

    private function freshUsername(): string
    {
        do {
            $username = 'farmer_' . strtolower(str()->random(10));
        } while (User::where('username', $username)->exists());

        return $username;
    }
}
