<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Auth\TokenAbility;
use App\Mail\AccountCodeMail;
use App\Models\ApiRequest;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/** Production with MAIL_MAILER=log: the state before the hosted email service is set up. */
function productionWithoutMailer(): void
{
    app()->detectEnvironment(fn () => 'production');
    config(['mail.default' => 'log']);
}

function farmerPayload(array $override = []): array
{
    return array_merge([
        'email' => 'farmer_' . strtolower(str()->random(6)) . '@example.com',
        'password' => 'long-enough-1',
        'name' => 'Amina',
        'consent' => true,
    ], $override);
}

function makeFarmer(string $password = 'long-enough-1', array $attributes = []): User
{
    return User::create(array_merge([
        'name' => 'Farmer',
        'username' => 'f_' . str()->random(8),
        'email' => 'f_' . strtolower(str()->random(6)) . '@example.com',
        'password' => Hash::make($password),
        'consent_at' => now(),
        'consent_version' => 'draft-1',
    ], $attributes));
}

function saveAdviceFor(?string $deviceToken, ?int $userId = null): ApiRequest
{
    return ApiRequest::create([
        'request_id' => (string)str()->uuid(),
        'device_token' => $deviceToken,
        'user_id' => $userId,
        'droid_request' => ['a' => 1],
        'plumber_request' => ['a' => 1],
        'plumber_response' => ['status' => 'success'],
    ]);
}

beforeEach(function () {
    RateLimiter::clear('sensitive|127.0.0.1');
    RateLimiter::clear('auth|127.0.0.1');
    Mail::fake();
});

// ── Register ─────────────────────────────────────────────────────────────────

it('registers a farmer with consent and returns a farmer token', function () {
    $payload = farmerPayload();

    $response = $this->postJson('/api/v1/account/register', $payload)
        ->assertCreated()
        ->assertJsonStructure(['token_type', 'token', 'expires_at', 'user' => ['id', 'name', 'email']])
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('consent_required', false);

    $user = User::where('email', $payload['email'])->firstOrFail();
    expect($user->consent_at)->not->toBeNull()
        ->and($user->consent_version)->toBe('draft-1')
        ->and($user->role->value)->toBe('user');

    $token = PersonalAccessToken::where('token', hash('sha256', $response->json('token')))->firstOrFail();
    expect($token->abilities)->toBe(TokenAbility::FARMER_ABILITIES);
});

it('rejects a duplicate email', function () {
    $existing = makeFarmer();

    $this->postJson('/api/v1/account/register', farmerPayload(['email' => $existing->email]))
        ->assertUnprocessable()->assertJsonValidationErrors('email');
});

it('rejects a weak password', function () {
    $this->postJson('/api/v1/account/register', farmerPayload(['password' => 'short']))
        ->assertUnprocessable()->assertJsonValidationErrors('password');
});

it('rejects a sign-up without consent', function () {
    $this->postJson('/api/v1/account/register', farmerPayload(['consent' => false]))
        ->assertUnprocessable()->assertJsonValidationErrors('consent');

    $payload = farmerPayload();
    unset($payload['consent']);
    $this->postJson('/api/v1/account/register', $payload)->assertUnprocessable();
});

it('sends a confirmation code on sign-up when email is on', function () {
    $payload = farmerPayload();
    $this->postJson('/api/v1/account/register', $payload)->assertCreated();

    Mail::assertSent(AccountCodeMail::class, fn ($m) => $m->hasTo($payload['email']) && $m->purpose === AccountCodeMail::VERIFY);
});

it('signs up without sending email while email is off', function () {
    productionWithoutMailer();

    $this->postJson('/api/v1/account/register', farmerPayload())->assertCreated();

    Mail::assertNothingSent();
});

// ── Login ────────────────────────────────────────────────────────────────────

it('signs a farmer in with the right password', function () {
    $user = makeFarmer();

    $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'long-enough-1'])
        ->assertOk()
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonPath('consent_required', false);
});

it('answers generically for a wrong password and an unknown email', function () {
    $user = makeFarmer();

    $a = $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'nope-nope-1'])->assertUnauthorized();
    $b = $this->postJson('/api/v1/account/login', ['email' => 'ghost@example.com', 'password' => 'nope-nope-1'])->assertUnauthorized();

    expect($a->json('message'))->toBe($b->json('message'));
});

it('does not let a Google-only account sign in with a blank or any password', function () {
    $user = makeFarmer('x', ['password' => null, 'google_id' => 'g-1']);

    $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'anything-1'])->assertUnauthorized();
});

it('throttles sign-in by IP', function () {
    config(['rate-limits.auth' => 2]);
    $user = makeFarmer();
    $body = ['email' => $user->email, 'password' => 'bad-password-1'];

    $this->postJson('/api/v1/account/login', $body)->assertUnauthorized();
    $this->postJson('/api/v1/account/login', $body)->assertUnauthorized();
    $this->postJson('/api/v1/account/login', $body)->assertStatus(429);
});

it('throttles sign-in by email across other IPs', function () {
    config(['rate-limits.auth' => 2]);
    $user = makeFarmer();
    RateLimiter::clear('account-login|' . sha1($user->email));
    $body = ['email' => $user->email, 'password' => 'bad-password-1'];

    $this->postJson('/api/v1/account/login', $body)->assertUnauthorized();
    $this->postJson('/api/v1/account/login', $body)->assertUnauthorized();

    RateLimiter::clear('auth|127.0.0.1'); // another IP would start a fresh IP counter
    $this->postJson('/api/v1/account/login', $body)->assertStatus(429);
});

it('forces an older account without consent to give it first', function () {
    $user = makeFarmer('long-enough-1', ['consent_at' => null, 'consent_version' => null]);
    $device = 'dev-' . str()->random(8);
    $advice = saveAdviceFor($device);

    $login = $this->postJson('/api/v1/account/login', [
        'email' => $user->email, 'password' => 'long-enough-1', 'device_token' => $device,
    ])->assertOk()->assertJsonPath('consent_required', true);

    expect($advice->fresh()->user_id)->toBeNull(); // nothing linked before consent

    $this->withToken($login->json('token'))
        ->postJson('/api/v1/account/consent', ['consent' => true, 'device_token' => $device])
        ->assertOk()->assertJsonPath('claimed_requests', 1);

    expect($user->fresh()->consent_at)->not->toBeNull()
        ->and($advice->fresh()->user_id)->toBe($user->id);
});

it('requires consent to be true on the consent route', function () {
    $user = makeFarmer('long-enough-1', ['consent_at' => null]);
    $token = $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'long-enough-1'])->json('token');

    $this->withToken($token)->postJson('/api/v1/account/consent', ['consent' => false])->assertUnprocessable();
});

// ── Logout ───────────────────────────────────────────────────────────────────

it('revokes the token on logout', function () {
    $user = makeFarmer();
    $token = $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'long-enough-1'])->json('token');

    $this->withToken($token)->postJson('/api/v1/account/logout')->assertOk();

    expect(PersonalAccessToken::where('token', hash('sha256', $token))->exists())->toBeFalse();
    $this->withToken($token)->postJson('/api/v1/account/logout')->assertUnauthorized();
});

it('keeps staff tokens and API keys out of the account routes', function () {
    $this->actingAsApiUser()->postJson('/api/v1/account/logout')->assertForbidden();
    $this->actingAsApiUser()->postJson('/api/v1/account/consent', ['consent' => true])->assertForbidden();
});

it('gives a farmer token no staff or admin access', function () {
    $user = makeFarmer();
    $token = $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'long-enough-1'])->json('token');

    $this->withToken($token)->getJson('/api/v1/admin/users')->assertForbidden();
    $this->withToken($token)->getJson('/api/v1/recommendations')->assertForbidden();
    $this->withToken($token)->getJson('/api/v1/translations')->assertForbidden();
});

// ── Email confirmation ───────────────────────────────────────────────────────

function lastCode(string $purpose): string
{
    $code = null;
    Mail::assertSent(AccountCodeMail::class, function ($m) use ($purpose, &$code) {
        if ($m->purpose === $purpose) {
            $code = $m->code;
        }

        return true;
    });

    return $code;
}

it('confirms the email address with the mailed code, once', function () {
    $payload = farmerPayload();
    $this->postJson('/api/v1/account/register', $payload)->assertCreated();
    $code = lastCode(AccountCodeMail::VERIFY);

    $this->postJson('/api/v1/account/email/verify', ['email' => $payload['email'], 'code' => 'WRONG000'])->assertUnprocessable();

    $this->postJson('/api/v1/account/email/verify', ['email' => $payload['email'], 'code' => $code])->assertOk();
    expect(User::where('email', $payload['email'])->first()->email_verified_at)->not->toBeNull();

    $this->postJson('/api/v1/account/email/verify', ['email' => $payload['email'], 'code' => $code])->assertUnprocessable();
});

it('resends a confirmation code to a signed-in farmer', function () {
    $user = makeFarmer();
    $token = $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'long-enough-1'])->json('token');

    $this->withToken($token)->postJson('/api/v1/account/email/resend')->assertOk();

    Mail::assertSent(AccountCodeMail::class, fn ($m) => $m->hasTo($user->email));
});

it('says email is unavailable on resend while email is off', function () {
    productionWithoutMailer();
    $user = makeFarmer();
    $token = $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'long-enough-1'])->json('token');

    $this->withToken($token)->postJson('/api/v1/account/email/resend')->assertStatus(503);
});

// ── Password reset ───────────────────────────────────────────────────────────

it('answers the same to a reset request for a known and an unknown address', function () {
    $user = makeFarmer();

    $a = $this->postJson('/api/v1/account/password/forgot', ['email' => $user->email])->assertOk();
    $b = $this->postJson('/api/v1/account/password/forgot', ['email' => 'ghost@example.com'])->assertOk();

    expect($a->json())->toBe($b->json());
    Mail::assertSent(AccountCodeMail::class, 1);
});

it('resets the password with the code, signs out every token and burns the code', function () {
    $user = makeFarmer();
    $old = $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'long-enough-1'])->json('token');

    $this->postJson('/api/v1/account/password/forgot', ['email' => $user->email])->assertOk();
    $code = lastCode(AccountCodeMail::RESET);

    $this->postJson('/api/v1/account/password/reset', ['email' => $user->email, 'code' => $code, 'password' => 'short'])
        ->assertUnprocessable();

    $this->postJson('/api/v1/account/password/reset', ['email' => $user->email, 'code' => $code, 'password' => 'brand-new-pass-2'])
        ->assertOk();

    $this->withToken($old)->postJson('/api/v1/account/logout')->assertUnauthorized();
    $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'long-enough-1'])->assertUnauthorized();
    $this->postJson('/api/v1/account/login', ['email' => $user->email, 'password' => 'brand-new-pass-2'])->assertOk();

    $this->postJson('/api/v1/account/password/reset', ['email' => $user->email, 'code' => $code, 'password' => 'another-pass-3'])
        ->assertUnprocessable();
});

it('rejects a reset with a wrong code', function () {
    $user = makeFarmer();
    $this->postJson('/api/v1/account/password/forgot', ['email' => $user->email])->assertOk();

    $this->postJson('/api/v1/account/password/reset', ['email' => $user->email, 'code' => 'WRONG000', 'password' => 'brand-new-pass-2'])
        ->assertUnprocessable();
});

it('sends no reset email while email is off but still answers the same', function () {
    productionWithoutMailer();
    $user = makeFarmer();

    $this->postJson('/api/v1/account/password/forgot', ['email' => $user->email])->assertOk();

    Mail::assertNothingSent();
});

// ── Claiming earlier advice on sign-up and sign-in ───────────────────────────

it('claims the phone advice on sign-up, once, and never takes another account rows', function () {
    $device = 'dev-' . str()->random(8);
    $other = makeFarmer();
    $mine = saveAdviceFor($device);
    $theirs = saveAdviceFor($device, $other->id);   // same phone token, but already someone else's
    $unrelated = saveAdviceFor('another-phone');

    $response = $this->postJson('/api/v1/account/register', farmerPayload(['device_token' => $device]))->assertCreated();
    $userId = $response->json('user.id');

    expect($mine->fresh()->user_id)->toBe($userId)
        ->and($theirs->fresh()->user_id)->toBe($other->id)
        ->and($unrelated->fresh()->user_id)->toBeNull()
        ->and($response->json('claimed_requests'))->toBe(1);

    // A second account signing in with the same token takes nothing more
    $second = makeFarmer();
    $again = $this->postJson('/api/v1/account/login', ['email' => $second->email, 'password' => 'long-enough-1', 'device_token' => $device])
        ->assertOk();

    expect($again->json('claimed_requests'))->toBe(0)
        ->and($mine->fresh()->user_id)->toBe($userId);
});
