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
use App\Repositories\ApiRequestRepo;
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

// ── A long history (a farmer with over a thousand saved items) ───────────────

/** Saves $count rows for a farmer in one insert, the oldest first at $count minutes ago. */
function bulkAdvice(int $userId, int $count): void
{
    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $rows[] = [
            'request_id' => (string)str()->uuid(),
            'device_token' => 'bulk',
            'user_id' => $userId,
            'droid_request' => json_encode(['n' => $i]),
            'plumber_request' => json_encode(['n' => $i]),
            'plumber_response' => json_encode(['status' => 'success']),
            'created_at' => now()->subMinutes($count - $i),
            'updated_at' => now(),
        ];
    }

    foreach (array_chunk($rows, 500) as $chunk) {
        ApiRequest::insert($chunk);
    }
}

it('pages through more than a thousand saved items without losing or repeating one', function () {
    [$me, $token] = signedInFarmer();
    bulkAdvice($me->id, 1200);
    saveAdviceFor('other', makeFarmer()->id); // someone else's row must not show up

    $seen = [];
    $page = 1;
    do {
        $response = $this->withToken($token)->getJson("/api/v1/account/history?per_page=100&page={$page}")->assertOk();
        array_push($seen, ...array_column($response->json('data'), 'request_id'));
        $last = $response->json('meta.last_page');
        $page++;
    } while ($page <= $last);

    expect($response->json('meta.total'))->toBe(1200)
        ->and($last)->toBe(12)
        ->and($seen)->toHaveCount(1200)
        ->and(array_unique($seen))->toHaveCount(1200);

    // newest first: the first item of the first page is the newest row
    $newest = ApiRequest::where('user_id', $me->id)->orderByDesc('created_at')->orderByDesc('id')->value('request_id');
    expect($seen[0])->toBe($newest);
});

it('reads only the columns the list shows, never the stored request and answer', function () {
    [$me, $token] = signedInFarmer();
    bulkAdvice($me->id, 5);

    DB::enableQueryLog();
    $this->withToken($token)->getJson('/api/v1/account/history')->assertOk();
    $queries = collect(DB::getQueryLog())->pluck('query')->filter(fn ($q) => str_contains($q, 'api_requests'))->implode(' | ');
    DB::disableQueryLog();

    expect($queries)->not->toContain('plumber_response')
        ->and($queries)->not->toContain('droid_request')
        ->and($queries)->not->toContain('plumber_request')
        ->and($queries)->toContain('request_id');
});

it('lists the ids of all saved items, newest first, and only the caller own', function () {
    [$me, $token] = signedInFarmer();
    bulkAdvice($me->id, 30);
    saveAdviceFor('other', makeFarmer()->id);

    $response = $this->withToken($token)->getJson('/api/v1/account/history/ids')->assertOk();

    $expected = ApiRequest::where('user_id', $me->id)->orderByDesc('created_at')->orderByDesc('id')->pluck('request_id')->all();
    expect($response->json('data'))->toBe($expected)
        ->and($response->json('meta.count'))->toBe(30)
        ->and($response->json('meta.truncated'))->toBeFalse();
});

it('says when the list of ids was cut', function () {
    [$me] = signedInFarmer();
    bulkAdvice($me->id, 5);

    $cut = app(ApiRequestRepo::class)->requestIdsForUser($me->id, 3);
    $all = app(ApiRequestRepo::class)->requestIdsForUser($me->id, 5);

    expect($cut['ids'])->toHaveCount(3)->and($cut['truncated'])->toBeTrue()
        ->and($all['ids'])->toHaveCount(5)->and($all['truncated'])->toBeFalse();
});

it('deletes all of the caller saved advice and keeps the account and everyone else rows', function () {
    [$me, $token] = signedInFarmer();
    $other = makeFarmer();
    bulkAdvice($me->id, 40);
    $theirs = saveAdviceFor('other', $other->id);
    $anonymous = saveAdviceFor('anon');

    $this->withToken($token)->deleteJson('/api/v1/account/history')
        ->assertOk()->assertJsonPath('deleted', 40);

    expect(ApiRequest::where('user_id', $me->id)->count())->toBe(0)
        ->and(User::find($me->id))->not->toBeNull()
        ->and($theirs->fresh())->not->toBeNull()
        ->and($anonymous->fresh())->not->toBeNull();
});

it('keeps the bulk history routes for farmer tokens with consent only', function () {
    $this->getJson('/api/v1/account/history/ids')->assertUnauthorized();
    $this->actingAsApiUser()->getJson('/api/v1/account/history/ids')->assertForbidden();
    $this->actingAsApiUser()->deleteJson('/api/v1/account/history')->assertForbidden();

    [$me, $token] = signedInFarmer(['consent_at' => null]);
    bulkAdvice($me->id, 3);
    $this->withToken($token)->deleteJson('/api/v1/account/history')->assertForbidden();
    expect(ApiRequest::where('user_id', $me->id)->count())->toBe(3);
});
