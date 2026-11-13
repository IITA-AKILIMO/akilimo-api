<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\Account\AccountLoginRequest;
use App\Http\Requests\Account\ConsentRequest;
use App\Http\Requests\Account\EmailVerifyRequest;
use App\Http\Requests\Account\PasswordForgotRequest;
use App\Http\Requests\Account\PasswordResetRequest;
use App\Http\Requests\Account\RegisterRequest;
use App\Http\Requests\Account\SocialSignInRequest;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Service\FarmerAccountService;
use App\Service\SocialSignInService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Optional farmer accounts: sign up, sign in, consent, email confirmation and password reset.
 * Responses carry the same shape as the staff login: token_type, token, expires_at, user.
 *
 * @tags Farmer accounts
 */
class AccountController extends Controller
{
    public function __construct(
        private readonly FarmerAccountService $accounts,
        private readonly SocialSignInService $socialSignIn,
    ) {}

    /**
     * Create a farmer account
     *
     * Needs `consent: true`. Pass the phone's `device_token` to keep the advice it saved before signing up.
     *
     * @unauthenticated
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        if ($limited = $this->limitedByEmail('register', $request->input('email'), (int)config('rate-limits.sensitive'))) {
            return $limited;
        }

        $result = $this->accounts->register($request->validated());

        return $this->tokenResponse($result, 201);
    }

    /**
     * Sign in a farmer
     *
     * Answers `consent_required: true` for an account that has not consented yet (for example an older playground
     * account): the token then only works for `POST /v1/account/consent` until consent is recorded.
     *
     * @unauthenticated
     */
    public function login(AccountLoginRequest $request): JsonResponse
    {
        if ($limited = $this->limitedByEmail('login', $request->input('email'), (int)config('rate-limits.auth'))) {
            return $limited;
        }

        $result = $this->accounts->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('device_token'),
        );

        if ($result === null) {
            return response()->json(['message' => 'Wrong email or password.'], 401);
        }

        return $this->tokenResponse($result);
    }

    /**
     * Sign in with Google
     *
     * Same as `POST /v1/account/social/google`. Send the Google OAuth `access_token` the app got from Google sign-in.
     *
     * @unauthenticated
     */
    public function google(SocialSignInRequest $request): JsonResponse
    {
        return $this->social($request, 'google');
    }

    /**
     * Sign in with a social provider (google, facebook, ...)
     *
     * The app signs the farmer in with the provider and sends that provider's OAuth `access_token`. A first sign-in creates
     * the account and needs `consent: true`; otherwise the answer is 422 with `consent_required`. A verified provider email
     * links to an existing account with the same address. Providers that are not switched on answer 404.
     *
     * @unauthenticated
     */
    public function social(SocialSignInRequest $request, string $provider): JsonResponse
    {
        if (!$this->socialSignIn->enabled($provider)) {
            return response()->json(['message' => 'This sign-in method is not available.'], 404);
        }

        $result = $this->socialSignIn->signIn(
            $provider,
            $request->input('access_token'),
            $request->boolean('consent'),
            $request->input('device_token'),
        );

        return match ($result) {
            SocialSignInService::FAILED => response()->json(['message' => 'Could not verify the sign-in.'], 401),
            SocialSignInService::CONSENT_REQUIRED => response()->json(['message' => 'Consent is needed to create the account.', 'code' => 'consent_required'], 422),
            SocialSignInService::NO_EMAIL => response()->json(['message' => 'The provider did not share an email address.', 'code' => 'no_email'], 422),
            SocialSignInService::EMAIL_UNVERIFIED => response()->json(['message' => 'An account with this email exists. Sign in with your password.', 'code' => 'email_unverified'], 409),
            default => $this->tokenResponse($result),
        };
    }

    /**
     * Record consent
     *
     * Records the farmer's consent and claims the advice the phone (`device_token`) saved earlier.
     */
    public function consent(ConsentRequest $request): JsonResponse
    {
        $claimed = $this->accounts->recordConsent($this->user($request), $request->input('device_token'));

        return response()->json(['message' => 'Consent recorded.', 'claimed_requests' => $claimed]);
    }

    /** Sign out: revoke the current token. */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->attributes->get('auth_token');

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Confirm the email address with the code that was mailed
     *
     * @unauthenticated
     */
    public function verifyEmail(EmailVerifyRequest $request): JsonResponse
    {
        if ($limited = $this->limitedByEmail('verify', $request->input('email'), (int)config('rate-limits.sensitive'))) {
            return $limited;
        }

        if (!$this->accounts->verifyEmail($request->input('email'), $request->input('code'))) {
            return response()->json(['message' => 'The code is wrong or has expired.'], 422);
        }

        return response()->json(['message' => 'Email address confirmed.']);
    }

    /** Send the confirmation code again to the signed-in farmer. */
    public function resendVerification(Request $request): JsonResponse
    {
        $user = $this->user($request);

        if ($user->email_verified_at !== null) {
            return response()->json(['message' => 'Email address already confirmed.']);
        }

        if ($limited = $this->limitedByEmail('resend', $user->email, (int)config('rate-limits.sensitive'))) {
            return $limited;
        }

        if (!$this->accounts->sendVerification($user)) {
            return response()->json(['message' => 'Email is not available yet.'], 503);
        }

        return response()->json(['message' => 'Confirmation code sent.']);
    }

    /**
     * Ask for a password reset code
     *
     * Always answers the same, whether or not the address has an account.
     *
     * @unauthenticated
     */
    public function forgotPassword(PasswordForgotRequest $request): JsonResponse
    {
        if ($limited = $this->limitedByEmail('forgot', $request->input('email'), (int)config('rate-limits.sensitive'))) {
            return $limited;
        }

        $this->accounts->sendReset($request->input('email'));

        return response()->json(['message' => 'If the address has an account, we sent a reset code.']);
    }

    /**
     * Set a new password with the mailed code
     *
     * Signs the account out everywhere.
     *
     * @unauthenticated
     */
    public function resetPassword(PasswordResetRequest $request): JsonResponse
    {
        if ($limited = $this->limitedByEmail('reset', $request->input('email'), (int)config('rate-limits.sensitive'))) {
            return $limited;
        }

        if (!$this->accounts->resetPassword($request->input('email'), $request->input('code'), $request->input('password'))) {
            return response()->json(['message' => 'The code is wrong or has expired.'], 422);
        }

        return response()->json(['message' => 'Password changed. Sign in again.']);
    }

    private function user(Request $request): User
    {
        return $request->user();
    }

    /** @param array{user: User, token: string, expires_at: Carbon, claimed: int} $result */
    private function tokenResponse(array $result, int $status = 200): JsonResponse
    {
        $user = $this->accounts->present($result['user']);

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $result['token'],
            'expires_at' => $result['expires_at']->toIso8601String(),
            'consent_required' => $user['consent_required'],
            'claimed_requests' => $result['claimed'],
            'user' => $user,
        ], $status);
    }

    /** A second limit besides the per-IP route limit: attempts per email address a minute, so one mailbox cannot be hammered from many IPs. */
    private function limitedByEmail(string $scope, string $email, int $max): ?JsonResponse
    {
        $key = "account-{$scope}|" . sha1(strtolower($email));

        if (RateLimiter::tooManyAttempts($key, $max)) {
            return response()->json(['message' => 'Too many attempts. Please wait a minute and try again.'], 429);
        }

        RateLimiter::hit($key, 60);

        return null;
    }
}
