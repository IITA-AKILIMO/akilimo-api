<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Models\ApiRequest;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Database\Seeders\FertilizerSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

function farmerToken(User $user, string $name = 'farmer', array $abilities = ['recommendations:compute', 'history:own'], $expires = null): string
{
    $raw = bin2hex(random_bytes(32));
    PersonalAccessToken::create([
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
        'name' => $name,
        'token' => hash('sha256', $raw),
        'abilities' => $abilities,
        'expires_at' => $expires ?? now()->addDay(),
    ]);

    return $raw;
}

beforeEach(function () {
    $this->seed(FertilizerSeeder::class);
    RateLimiter::clear('compute|127.0.0.1');
    Http::fake(['*' => Http::response(plumberSuccessResponse(), 200)]);
});

it('still computes for an anonymous caller and links the advice to nobody', function () {
    $payload = validComputePayload();

    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();

    expect(ApiRequest::where('device_token', $payload['user_info']['device_token'])->firstOrFail()->user_id)->toBeNull();
});

it('links the advice to a signed-in farmer', function () {
    $farmer = makeFarmer();

    $this->withToken(farmerToken($farmer))->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    expect(ApiRequest::where('user_id', $farmer->id)->count())->toBe(1);
});

it('gives each farmer a saved row even for identical farm inputs', function () {
    $a = makeFarmer();
    $b = makeFarmer();
    $payload = validComputePayload();

    $this->withToken(farmerToken($a))->postJson('/api/v1/recommendations/compute', $payload)->assertOk();
    $this->withToken(farmerToken($b))->postJson('/api/v1/recommendations/compute', $payload)->assertOk();

    expect(ApiRequest::where('user_id', $a->id)->count())->toBe(1)
        ->and(ApiRequest::where('user_id', $b->id)->count())->toBe(1);
});

it('treats a staff token, an API key style bearer and an unknown token as anonymous, without failing', function () {
    $farmer = makeFarmer();
    $staff = farmerToken($farmer, 'login', ['*']);

    $this->withToken($staff)->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();
    $this->withToken('ak_not-a-real-key')->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();
    $this->withToken('garbage')->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    expect(ApiRequest::whereNotNull('user_id')->count())->toBe(0);
});

it('treats an expired farmer token as anonymous', function () {
    $farmer = makeFarmer();
    $token = farmerToken($farmer, 'farmer', ['recommendations:compute', 'history:own'], now()->subMinute());

    $this->withToken($token)->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    expect(ApiRequest::whereNotNull('user_id')->count())->toBe(0);
});

it('does not link advice for a farmer who has not consented yet', function () {
    $farmer = makeFarmer('long-enough-1', ['consent_at' => null]);

    $this->withToken(farmerToken($farmer))->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    expect(ApiRequest::whereNotNull('user_id')->count())->toBe(0);
});

it('keeps a farmer token without the compute ability anonymous', function () {
    $farmer = makeFarmer();

    $this->withToken(farmerToken($farmer, 'farmer', ['history:own']))->postJson('/api/v1/recommendations/compute', validComputePayload())->assertOk();

    expect(ApiRequest::whereNotNull('user_id')->count())->toBe(0);
});
