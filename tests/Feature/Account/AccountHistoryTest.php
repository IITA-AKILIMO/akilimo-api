<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Models\ApiKey;
use App\Models\ApiRequest;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    foreach (['read', 'write', 'sensitive'] as $limiter) {
        RateLimiter::clear("{$limiter}|127.0.0.1");
    }
});

function signedInFarmer(array $attributes = []): array
{
    $user = makeFarmer('long-enough-1', $attributes);

    return [$user, farmerToken($user)];
}

// ── History ──────────────────────────────────────────────────────────────────

it('lists only the caller saved advice, newest first, paginated', function () {
    [$me, $token] = signedInFarmer();
    $other = makeFarmer();

    $old = saveAdviceFor('d1', $me->id);
    $old->forceFill(['created_at' => now()->subDay()])->save();
    $new = saveAdviceFor('d1', $me->id);
    saveAdviceFor('d2', $other->id);
    saveAdviceFor('d3', null);

    $response = $this->withToken($token)->getJson('/api/v1/account/history')->assertOk();

    expect($response->json('data'))->toHaveCount(2)
        ->and($response->json('data.0.request_id'))->toBe($new->request_id)
        ->and($response->json('data.1.request_id'))->toBe($old->request_id)
        ->and($response->json('data.0'))->not->toHaveKey('result')
        ->and($response->json('meta.total'))->toBe(2);

    $this->withToken($token)->getJson('/api/v1/account/history?per_page=1')->assertOk()->assertJsonCount(1, 'data');
});

it('shows one saved recommendation with its result', function () {
    [$me, $token] = signedInFarmer();
    $row = saveAdviceFor('d1', $me->id);

    $this->withToken($token)->getJson("/api/v1/account/history/{$row->request_id}")
        ->assertOk()
        ->assertJsonPath('data.request_id', $row->request_id)
        ->assertJsonPath('data.result.status', 'success');
});

it('answers 404 for someone else saved advice or an unknown id, and never leaks it', function () {
    [$me, $token] = signedInFarmer();
    $theirs = saveAdviceFor('d2', makeFarmer()->id);
    $anonymous = saveAdviceFor('d3');

    $this->withToken($token)->getJson("/api/v1/account/history/{$theirs->request_id}")->assertNotFound();
    $this->withToken($token)->getJson("/api/v1/account/history/{$anonymous->request_id}")->assertNotFound();
    $this->withToken($token)->getJson('/api/v1/account/history/' . str()->uuid())->assertNotFound();
    $this->withToken($token)->deleteJson("/api/v1/account/history/{$theirs->request_id}")->assertNotFound();

    expect(ApiRequest::find($theirs->id))->not->toBeNull();
});

it('deletes one saved item and only that one', function () {
    [$me, $token] = signedInFarmer();
    $a = saveAdviceFor('d1', $me->id);
    $b = saveAdviceFor('d1', $me->id);

    $this->withToken($token)->deleteJson("/api/v1/account/history/{$a->request_id}")->assertOk();

    expect(ApiRequest::find($a->id))->toBeNull()
        ->and(ApiRequest::find($b->id))->not->toBeNull();
});

it('needs a token, and a farmer token, to reach the history', function () {
    $this->getJson('/api/v1/account/history')->assertUnauthorized();
    $this->actingAsApiUser()->getJson('/api/v1/account/history')->assertForbidden();
    $this->actingAsApiUser()->deleteJson('/api/v1/account')->assertForbidden();
});

it('needs the history:own ability', function () {
    $user = makeFarmer();

    $this->withToken(farmerToken($user, 'farmer', ['recommendations:compute']))->getJson('/api/v1/account/history')->assertForbidden();
});

it('returns no history until the farmer has consented', function () {
    [$me, $token] = signedInFarmer(['consent_at' => null]);
    saveAdviceFor('d1', $me->id);

    $this->withToken($token)->getJson('/api/v1/account/history')
        ->assertForbidden()->assertJsonPath('code', 'consent_required');
    $this->withToken($token)->deleteJson('/api/v1/account')->assertForbidden();
});

it('keeps a farmer token out of the staff recommendation list, other farmers routes and admin', function () {
    [$me, $token] = signedInFarmer();

    $this->withToken($token)->getJson('/api/v1/recommendations')->assertForbidden();
    $this->withToken($token)->getJson('/api/v1/user-feedback')->assertForbidden();
    $this->withToken($token)->getJson('/api/v1/admin/users')->assertForbidden();
    $this->withToken($token)->deleteJson('/api/v1/admin/users/' . $me->id)->assertForbidden();
});

it('throttles the history reads', function () {
    config(['rate-limits.read' => 1]);
    [$me, $token] = signedInFarmer();

    $this->withToken($token)->getJson('/api/v1/account/history')->assertOk();
    $this->withToken($token)->getJson('/api/v1/account/history')->assertStatus(429);
});

// ── Account deletion ─────────────────────────────────────────────────────────

it('deletes the account and keeps its requests as anonymous statistics', function () {
    [$me, $token] = signedInFarmer();
    $other = makeFarmer();
    $kept = saveAdviceFor('phone-1', $me->id);
    $othersRow = saveAdviceFor('phone-2', $other->id);
    UserIdentity::create(['user_id' => $me->id, 'provider' => 'google', 'subject' => 'g-1']);
    ApiKey::create([
        'user_id' => $me->id, 'name' => 'k', 'key_hash' => hash('sha256', 'ak_x'), 'key_prefix' => 'ak_x',
        'abilities' => ['*'], 'is_active' => true,
    ]);

    $this->withToken($token)->deleteJson('/api/v1/account')->assertOk();

    expect(User::find($me->id))->toBeNull()
        ->and(PersonalAccessToken::where('tokenable_id', $me->id)->count())->toBe(0)
        ->and(UserIdentity::where('user_id', $me->id)->count())->toBe(0)
        ->and(ApiKey::where('user_id', $me->id)->count())->toBe(0)
        ->and($kept->fresh())->not->toBeNull()
        ->and($kept->fresh()->user_id)->toBeNull()
        ->and($kept->fresh()->device_token)->toBeNull()
        ->and($kept->fresh()->plumber_response)->toBe(['status' => 'success'])
        ->and($othersRow->fresh()->user_id)->toBe($other->id)
        ->and($othersRow->fresh()->device_token)->toBe('phone-2');

    $this->withToken($token)->getJson('/api/v1/account/history')->assertUnauthorized();
});

it('cannot reclaim the kept rows after deletion, because the device token is gone', function () {
    [$me, $token] = signedInFarmer();
    $kept = saveAdviceFor('phone-1', $me->id);

    $this->withToken($token)->deleteJson('/api/v1/account')->assertOk();

    $again = $this->postJson('/api/v1/account/register', farmerPayload(['device_token' => 'phone-1']))->assertCreated();

    expect($again->json('claimed_requests'))->toBe(0)
        ->and($kept->fresh()->user_id)->toBeNull();
});
