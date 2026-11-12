<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Auth\TokenAbility;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialProfile;

/**
 * Stand in for a Socialite driver: no network. $profiles maps an access token to a profile (or to an exception to throw).
 *
 * @param array<string, SocialProfile|Throwable> $profiles
 */
function fakeSocialDriver(string $driver, array $profiles): void
{
    Socialite::extend($driver, fn () => new class($profiles) implements Provider {
        public function __construct(private array $profiles) {}

        public function redirect() {}

        public function user() {}

        public function stateless(): static
        {
            return $this;
        }

        public function userFromToken(string $token)
        {
            $profile = $this->profiles[$token] ?? throw new RuntimeException('bad token');

            if ($profile instanceof Throwable) {
                throw $profile;
            }

            return $profile;
        }
    });
}

function socialProfile(string $id, string $email, bool $verified = true, string $name = 'Amina'): SocialProfile
{
    return (new SocialProfile)
        ->setRaw(['verified_email' => $verified, 'email_verified' => $verified])
        ->map(['id' => $id, 'name' => $name, 'email' => $email]);
}

/** Google's tokeninfo answer for a token issued to the given client */
function fakeTokenInfo(string $aud, int $status = 200): void
{
    Http::swap(new HttpFactory); // a fresh fake: the first matching stub wins, so an earlier one would shadow this
    Http::fake(['oauth2.googleapis.com/tokeninfo*' => Http::response($status === 200 ? ['aud' => $aud, 'azp' => $aud] : ['error' => 'invalid_token'], $status)]);
}

beforeEach(function () {
    RateLimiter::clear('auth|127.0.0.1');
    config([
        'farmer-accounts.social_providers' => ['google', 'facebook'],
        'farmer-accounts.client_ids.google' => ['our-client.apps.googleusercontent.com'],
    ]);
    fakeTokenInfo('our-client.apps.googleusercontent.com');
});

it('keeps social sign-in off until a provider is switched on', function () {
    config(['farmer-accounts.social_providers' => []]);

    $this->postJson('/api/v1/account/google', ['access_token' => 'tok', 'consent' => true])->assertNotFound();
    $this->postJson('/api/v1/account/social/github', ['access_token' => 'tok', 'consent' => true])->assertNotFound();
});

it('creates an account on the first Google sign-in with consent and issues a farmer token', function () {
    fakeSocialDriver('google', ['tok' => socialProfile('g-1', 'Amina@Example.com')]);

    $response = $this->postJson('/api/v1/account/google', ['access_token' => 'tok', 'consent' => true])
        ->assertOk()
        ->assertJsonPath('user.email', 'amina@example.com')
        ->assertJsonPath('user.email_verified', true)
        ->assertJsonPath('consent_required', false);

    $user = User::where('email', 'amina@example.com')->firstOrFail();
    expect($user->password)->toBeNull()
        ->and($user->consent_at)->not->toBeNull()
        ->and(UserIdentity::where(['provider' => 'google', 'subject' => 'g-1', 'user_id' => $user->id])->exists())->toBeTrue();

    $token = PersonalAccessToken::where('token', hash('sha256', $response->json('token')))->firstOrFail();
    expect($token->abilities)->toBe(TokenAbility::FARMER_ABILITIES);
});

it('asks for consent on a first Google sign-in without it, and creates nothing', function () {
    fakeSocialDriver('google', ['tok' => socialProfile('g-1', 'amina@example.com')]);

    $this->postJson('/api/v1/account/google', ['access_token' => 'tok'])
        ->assertUnprocessable()->assertJsonPath('code', 'consent_required');

    expect(User::where('email', 'amina@example.com')->exists())->toBeFalse()
        ->and(UserIdentity::count())->toBe(0);
});

it('signs the same Google person in again without a new account', function () {
    fakeSocialDriver('google', ['tok' => socialProfile('g-1', 'amina@example.com')]);
    $first = $this->postJson('/api/v1/account/google', ['access_token' => 'tok', 'consent' => true])->json('user.id');

    $this->postJson('/api/v1/account/google', ['access_token' => 'tok'])
        ->assertOk()->assertJsonPath('user.id', $first);

    expect(User::where('email', 'amina@example.com')->count())->toBe(1)
        ->and(UserIdentity::count())->toBe(1);
});

it('rejects a token issued to another app (wrong audience)', function () {
    fakeSocialDriver('google', ['tok' => socialProfile('g-1', 'amina@example.com')]);
    fakeTokenInfo('someone-elses-client.apps.googleusercontent.com');

    $this->postJson('/api/v1/account/google', ['access_token' => 'tok', 'consent' => true])->assertUnauthorized();

    expect(User::where('email', 'amina@example.com')->exists())->toBeFalse();
});

it('rejects an expired or unknown token', function () {
    fakeSocialDriver('google', ['tok' => socialProfile('g-1', 'amina@example.com')]);
    fakeTokenInfo('', 400); // Google no longer knows the token

    $this->postJson('/api/v1/account/google', ['access_token' => 'tok', 'consent' => true])->assertUnauthorized();

    fakeTokenInfo('our-client.apps.googleusercontent.com');
    $this->postJson('/api/v1/account/google', ['access_token' => 'unknown', 'consent' => true])->assertUnauthorized();
});

it('refuses Google sign-in while no client ID is configured', function () {
    config(['farmer-accounts.client_ids.google' => []]);
    fakeSocialDriver('google', ['tok' => socialProfile('g-1', 'amina@example.com')]);

    $this->postJson('/api/v1/account/google', ['access_token' => 'tok', 'consent' => true])->assertUnauthorized();
});

it('links Google to an existing account with the same verified email', function () {
    $existing = makeFarmer('long-enough-1', ['email' => 'amina@example.com', 'email_verified_at' => null]);
    fakeSocialDriver('google', ['tok' => socialProfile('g-1', 'amina@example.com', true)]);

    $this->postJson('/api/v1/account/google', ['access_token' => 'tok'])
        ->assertOk()->assertJsonPath('user.id', $existing->id)->assertJsonPath('user.email_verified', true);

    expect(User::where('email', 'amina@example.com')->count())->toBe(1)
        ->and(UserIdentity::where(['provider' => 'google', 'user_id' => $existing->id])->exists())->toBeTrue();
});

it('does not link or take over an account for an unverified Google email', function () {
    $existing = makeFarmer('long-enough-1', ['email' => 'amina@example.com']);
    fakeSocialDriver('google', ['tok' => socialProfile('g-9', 'amina@example.com', false)]);

    $this->postJson('/api/v1/account/google', ['access_token' => 'tok', 'consent' => true])
        ->assertStatus(409)->assertJsonPath('code', 'email_unverified');

    expect(UserIdentity::count())->toBe(0)
        ->and($existing->fresh()->email_verified_at)->toBeNull();
});

it('answers consent_required for an older account without consent and links nothing before it', function () {
    makeFarmer('long-enough-1', ['email' => 'amina@example.com', 'consent_at' => null, 'consent_version' => null]);
    $device = 'dev-' . str()->random(8);
    $advice = saveAdviceFor($device);
    fakeSocialDriver('google', ['tok' => socialProfile('g-1', 'amina@example.com')]);

    $this->postJson('/api/v1/account/google', ['access_token' => 'tok', 'device_token' => $device])
        ->assertOk()->assertJsonPath('consent_required', true);

    expect($advice->fresh()->user_id)->toBeNull();
});

it('claims the phone advice on a first Google sign-in', function () {
    $device = 'dev-' . str()->random(8);
    $advice = saveAdviceFor($device);
    fakeSocialDriver('google', ['tok' => socialProfile('g-1', 'amina@example.com')]);

    $response = $this->postJson('/api/v1/account/google', ['access_token' => 'tok', 'consent' => true, 'device_token' => $device])
        ->assertOk()->assertJsonPath('claimed_requests', 1);

    expect($advice->fresh()->user_id)->toBe($response->json('user.id'));
});

it('signs in with another provider through the generic route', function () {
    fakeSocialDriver('facebook', ['fbtok' => socialProfile('fb-7', 'kofi@example.com', true, 'Kofi')]);

    $this->postJson('/api/v1/account/social/facebook', ['access_token' => 'fbtok', 'consent' => true])
        ->assertOk()->assertJsonPath('user.email', 'kofi@example.com');

    expect(UserIdentity::where(['provider' => 'facebook', 'subject' => 'fb-7'])->exists())->toBeTrue();
});

it('needs an email address from the provider', function () {
    fakeSocialDriver('facebook', ['fbtok' => (new SocialProfile)->setRaw([])->map(['id' => 'fb-8', 'name' => 'No Email', 'email' => null])]);

    $this->postJson('/api/v1/account/social/facebook', ['access_token' => 'fbtok', 'consent' => true])
        ->assertUnprocessable()->assertJsonPath('code', 'no_email');
});

it('removes the linked identities when the account is deleted', function () {
    fakeSocialDriver('google', ['tok' => socialProfile('g-1', 'amina@example.com')]);
    $this->postJson('/api/v1/account/google', ['access_token' => 'tok', 'consent' => true])->assertOk();

    User::where('email', 'amina@example.com')->firstOrFail()->delete();

    expect(UserIdentity::count())->toBe(0);
});
