<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\ApiRequest;
use App\Models\Message;
use App\Models\PersonalAccessToken;
use App\Models\PushDevice;
use App\Models\User;
use App\Service\Fcm\FcmClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

function pushFarmer(string $name = 'Amina Farmer', bool $consent = true): User
{
    return User::create([
        'name' => $name,
        'username' => 'farmer_' . Str::random(8),
        'email' => 'farmer_' . Str::random(8) . '@example.com',
        'password' => bcrypt('password'),
        'role' => EnumUserRole::User,
        'consent_at' => $consent ? now() : null,
    ]);
}

function pushFarmerToken(User $user): string
{
    $raw = bin2hex(random_bytes(32));
    PersonalAccessToken::create([
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
        'name' => 'farmer',
        'token' => hash('sha256', $raw),
        'abilities' => ['recommendations:compute', 'history:own'],
        'expires_at' => now()->addDay(),
    ]);

    return $raw;
}

function pushDevice(array $attributes = []): PushDevice
{
    return PushDevice::create([
        'device_token' => 'install-' . Str::random(12),
        'fcm_token' => 'fcm-' . Str::random(20),
        ...$attributes,
    ]);
}

function enableFcm(): void
{
    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($key, $pem);
    config(['fcm.enabled' => true, 'fcm.project_id' => 'p', 'fcm.credentials' => json_encode(['client_email' => 'a@b.c', 'private_key' => $pem])]);
    cache()->forget('fcm.access_token');
}

beforeEach(fn () => RateLimiter::clear('write|127.0.0.1'));

// ── PUT / DELETE /v1/devices ─────────────────────────────────────────────────

it('registers a phone, refreshes it and keeps one row per device_token', function () {
    $payload = ['device_token' => 'install-1', 'fcm_token' => 'fcm-a', 'app_version' => '1.4.0', 'country' => 'tz', 'language' => 'SW'];

    $this->putJson('/api/v1/devices', $payload)->assertOk()->assertJsonPath('data.linked_to_account', false)->assertJsonPath('data.country', 'TZ');
    $this->putJson('/api/v1/devices', [...$payload, 'fcm_token' => 'fcm-b', 'language' => 'rw'])->assertOk();

    $device = PushDevice::where('device_token', 'install-1')->sole();
    expect($device->fcm_token)->toBe('fcm-b')->and($device->language)->toBe('rw')->and($device->platform)->toBe('android')
        ->and($device->last_seen_at)->not->toBeNull();
});

it('links the phone to a signed-in farmer and clears the link without a token', function () {
    $farmer = pushFarmer();
    $payload = ['device_token' => 'install-2', 'fcm_token' => 'fcm-c'];

    $this->withToken(pushFarmerToken($farmer))->putJson('/api/v1/devices', $payload)->assertOk()->assertJsonPath('data.linked_to_account', true);
    expect(PushDevice::where('device_token', 'install-2')->value('user_id'))->toBe($farmer->id);

    $this->flushHeaders()->putJson('/api/v1/devices', $payload)->assertOk();
    expect(PushDevice::where('device_token', 'install-2')->value('user_id'))->toBeNull();
});

it('does not link a farmer who has not consented', function () {
    $farmer = pushFarmer(consent: false);

    $this->withToken(pushFarmerToken($farmer))->putJson('/api/v1/devices', ['device_token' => 'install-3', 'fcm_token' => 'fcm-d'])->assertOk();

    expect(PushDevice::where('device_token', 'install-3')->value('user_id'))->toBeNull();
});

it('drops the same Firebase token under another device_token (a reinstall)', function () {
    $this->putJson('/api/v1/devices', ['device_token' => 'old-install', 'fcm_token' => 'fcm-same'])->assertOk();
    $this->putJson('/api/v1/devices', ['device_token' => 'new-install', 'fcm_token' => 'fcm-same'])->assertOk();

    expect(PushDevice::pluck('device_token')->all())->toBe(['new-install']);
});

it('validates what a phone registers', function () {
    $this->putJson('/api/v1/devices', [])->assertStatus(422)->assertJsonValidationErrors(['device_token', 'fcm_token']);
    $this->putJson('/api/v1/devices', ['device_token' => 'x', 'fcm_token' => 'y', 'country' => 'TZA', 'platform' => 'ios'])
        ->assertJsonValidationErrors(['country', 'platform']);
});

it('unregisters a phone and answers 204 even when it is unknown', function () {
    $device = pushDevice();

    $this->deleteJson('/api/v1/devices', ['device_token' => $device->device_token])->assertNoContent();
    $this->deleteJson('/api/v1/devices', ['device_token' => $device->device_token])->assertNoContent();
    $this->deleteJson('/api/v1/devices', [])->assertStatus(422);

    expect(PushDevice::count())->toBe(0);
});

it('throttles device registration', function () {
    RateLimiter::clear('write|127.0.0.1');
    $limit = (int)config('rate-limits.write');

    for ($i = 0; $i < $limit; $i++) {
        $this->putJson('/api/v1/devices', ['device_token' => "t{$i}", 'fcm_token' => "f{$i}"])->assertOk();
    }

    $this->putJson('/api/v1/devices', ['device_token' => 'over', 'fcm_token' => 'over'])->assertStatus(429);
});

// ── GET /v1/messages: private messages ───────────────────────────────────────

it('never returns a private message to anyone else', function () {
    $farmer = pushFarmer();
    $device = pushDevice(['user_id' => $farmer->id]);
    $other = pushDevice();
    Message::create(['type' => 'advice', 'title' => 'public', 'body' => 'b']);
    Message::create(['type' => 'advice', 'title' => 'to phone', 'body' => 'b', 'recipient_device_token' => $other->device_token]);
    Message::create(['type' => 'advice', 'title' => 'to farmer', 'body' => 'b', 'recipient_user_id' => $farmer->id]);

    $titles = fn (string $query) => collect($this->getJson('/api/v1/messages' . $query)->assertOk()->json('data'))->pluck('title')->sort()->values()->all();

    expect($titles(''))->toBe(['public'])
        ->and($titles('?device_token=unknown'))->toBe(['public'])
        ->and($titles('?device_token=' . $device->device_token))->toBe(['public', 'to farmer'])
        ->and($titles('?device_token=' . $other->device_token))->toBe(['public', 'to phone']);
});

it('stops giving a farmer message to a phone that signed out', function () {
    $farmer = pushFarmer();
    $device = pushDevice(['user_id' => $farmer->id]);
    Message::create(['type' => 'advice', 'title' => 'to farmer', 'body' => 'b', 'recipient_user_id' => $farmer->id]);

    $device->forceFill(['user_id' => null])->save();

    expect($this->getJson('/api/v1/messages?device_token=' . $device->device_token)->assertOk()->json('data'))->toBe([]);
});

// ── FcmClient ────────────────────────────────────────────────────────────────

it('sends to one token and removes a token FCM reports as UNREGISTERED', function () {
    enableFcm();
    $gone = pushDevice(['fcm_token' => 'fcm-gone']);
    $kept = pushDevice(['fcm_token' => 'fcm-kept']);
    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 'at'], 200),
        'fcm.googleapis.com/*' => Http::sequence()
            ->push(['name' => 'ok'], 200)
            ->push(['error' => ['status' => 'NOT_FOUND', 'details' => [['errorCode' => 'UNREGISTERED']]]], 404),
    ]);
    $client = app(FcmClient::class);

    expect($client->sendToToken($kept->fcm_token, ['id' => '1']))->toBeTrue()
        ->and($client->sendToToken($gone->fcm_token, ['id' => '1']))->toBeFalse();

    expect(PushDevice::pluck('fcm_token')->all())->toBe(['fcm-kept']);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'messages:send') && ($r['message']['token'] ?? null) === 'fcm-kept' && $r['message']['data'] === ['id' => '1']);
});

it('throws for any other FCM failure and keeps the token', function () {
    enableFcm();
    $device = pushDevice();
    Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'at'], 200), 'fcm.googleapis.com/*' => Http::response(['error' => 'down'], 500)]);

    expect(fn () => app(FcmClient::class)->sendToToken($device->fcm_token, ['id' => '1']))->toThrow(RuntimeException::class);
    expect(PushDevice::count())->toBe(1);
});

// ── POST /v1/admin/messages with recipients ──────────────────────────────────

it('stores a private message per recipient and pushes every phone of an account', function () {
    enableFcm();
    $farmer = pushFarmer();
    pushDevice(['user_id' => $farmer->id, 'fcm_token' => 'fcm-1']);
    pushDevice(['user_id' => $farmer->id, 'fcm_token' => 'fcm-2']);
    $single = pushDevice(['fcm_token' => 'fcm-3']);
    Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'at'], 200), 'fcm.googleapis.com/*' => Http::response(['name' => 'ok'], 200)]);
    $this->actingAsApiUser();

    $this->postJson('/api/v1/admin/messages', [
        'type' => 'advice', 'title' => 'Hello', 'body' => 'Private',
        'recipients' => [['type' => 'user', 'id' => $farmer->id], ['type' => 'device', 'id' => $single->device_token]],
    ])->assertCreated()->assertJsonPath('push', 'sent')->assertJsonPath('devices', 3)->assertJsonCount(2, 'data');

    expect(Message::where('recipient_user_id', $farmer->id)->sole()->pushed_at)->not->toBeNull()
        ->and(Message::where('recipient_device_token', $single->device_token)->count())->toBe(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'messages:send') && ($r['message']['token'] ?? null) === 'fcm-3');
});

it('keeps the message when a private push fails or no phone is registered', function () {
    enableFcm();
    $farmer = pushFarmer();
    $nobody = pushFarmer('No Phone');
    pushDevice(['user_id' => $farmer->id]);
    Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'at'], 200), 'fcm.googleapis.com/*' => Http::response(['error' => 'down'], 500)]);
    $this->actingAsApiUser();
    $body = ['type' => 'advice', 'title' => 'T', 'body' => 'B'];

    $this->postJson('/api/v1/admin/messages', [...$body, 'recipients' => [['type' => 'user', 'id' => $farmer->id]]])
        ->assertCreated()->assertJsonPath('push', 'failed')->assertJsonPath('devices', 0);
    $this->postJson('/api/v1/admin/messages', [...$body, 'recipients' => [['type' => 'user', 'id' => $nobody->id]]])
        ->assertCreated()->assertJsonPath('push', 'no_audience');

    expect(Message::count())->toBe(2)->and(Message::whereNotNull('pushed_at')->count())->toBe(0);
});

it('stores a private message and reports disabled when push is off', function () {
    config(['fcm.enabled' => false]);
    $device = pushDevice();
    $this->actingAsApiUser();

    $this->postJson('/api/v1/admin/messages', ['type' => 'advice', 'title' => 'T', 'body' => 'B', 'recipients' => [['type' => 'device', 'id' => $device->device_token]]])
        ->assertCreated()->assertJsonPath('push', 'disabled');
});

it('validates recipients', function () {
    $this->actingAsApiUser();
    $base = ['type' => 'advice', 'title' => 'T', 'body' => 'B'];

    $this->postJson('/api/v1/admin/messages', [...$base, 'recipients' => [['type' => 'user', 'id' => '999999']]])->assertJsonValidationErrors(['recipients.0.id']);
    $this->postJson('/api/v1/admin/messages', [...$base, 'recipients' => [['type' => 'device', 'id' => 'nope']]])->assertJsonValidationErrors(['recipients.0.id']);
    $this->postJson('/api/v1/admin/messages', [...$base, 'recipients' => [['type' => 'robot', 'id' => '1']]])->assertJsonValidationErrors(['recipients.0.type']);
    $this->postJson('/api/v1/admin/messages', [...$base, 'language' => 'sw', 'recipients' => [['type' => 'user', 'id' => '1']]])->assertJsonValidationErrors(['recipients']);

    // An admin account is not a farmer account
    $admin = User::create(['name' => 'A', 'username' => 'adm_' . Str::random(6), 'email' => 'adm_' . Str::random(6) . '@example.com', 'password' => bcrypt('x'), 'role' => EnumUserRole::Admin]);
    $this->postJson('/api/v1/admin/messages', [...$base, 'recipients' => [['type' => 'user', 'id' => $admin->id]]])->assertJsonValidationErrors(['recipients.0.id']);

    expect(Message::count())->toBe(0);
});

// ── Admin page ───────────────────────────────────────────────────────────────

it('searches farmers by name, email and phone, and devices by token, with phone counts', function () {
    $this->withoutVite();
    $farmer = pushFarmer('Zainabu Mwakyusa');
    ApiRequest::create([
        'request_id' => (string)Str::uuid(), 'device_token' => 'x', 'user_id' => $farmer->id,
        'droid_request' => ['user_info' => ['phone_number' => '+255712345678']], 'plumber_request' => ['a' => 1], 'plumber_response' => ['status' => 'success'],
    ]);
    pushDevice(['user_id' => $farmer->id]);
    pushDevice(['user_id' => $farmer->id]);
    $device = pushDevice(['device_token' => 'abcd1234-zzz', 'country' => 'TZ', 'language' => 'sw']);
    $admin = User::create(['name' => 'Adm', 'username' => 'adm_' . Str::random(6), 'email' => 'adm_' . Str::random(6) . '@example.com', 'password' => bcrypt('x'), 'role' => EnumUserRole::Admin]);
    $this->actingAs($admin);

    foreach (['Zainabu', $farmer->email, '71234567'] as $q) {
        $rows = $this->getJson('/admin/messages/recipients?q=' . urlencode($q))->assertOk()->json('data');
        expect($rows)->toHaveCount(1)->and($rows[0]['type'])->toBe('user')->and($rows[0]['devices'])->toBe(2);
    }
    $rows = $this->getJson('/admin/messages/recipients?q=abcd1234')->assertOk()->json('data');
    expect($rows)->toHaveCount(1)->and($rows[0])->toMatchArray(['type' => 'device', 'id' => $device->device_token, 'devices' => 1]);
    expect($this->getJson('/admin/messages/recipients?q=z')->json('data'))->toBe([]);
});

it('counts distinct phones for the chosen recipients and sends private messages from the form', function () {
    $this->withoutVite();
    config(['fcm.enabled' => false]);
    $farmer = pushFarmer();
    $own = pushDevice(['user_id' => $farmer->id]);
    $admin = User::create(['name' => 'Adm', 'username' => 'adm_' . Str::random(6), 'email' => 'adm_' . Str::random(6) . '@example.com', 'password' => bcrypt('x'), 'role' => EnumUserRole::Admin]);
    $this->actingAs($admin);
    $recipients = [['type' => 'user', 'id' => (string)$farmer->id], ['type' => 'device', 'id' => $own->device_token]];

    $this->getJson('/admin/messages/audience?' . http_build_query(['recipients' => $recipients]))->assertOk()->assertJson(['devices' => 1]);

    $this->post('/admin/messages', ['type' => 'advice', 'title' => 'Hi', 'body' => 'There', 'recipients' => $recipients])
        ->assertRedirect('/admin/messages')->assertSessionHas('success');
    expect(Message::count())->toBe(2);
});

it('keeps non-admins out of the recipient search', function () {
    $partner = User::create(['name' => 'P', 'username' => 'p_' . Str::random(6), 'email' => 'p_' . Str::random(6) . '@example.com', 'password' => bcrypt('x'), 'role' => EnumUserRole::Partner]);
    $this->actingAs($partner)->getJson('/admin/messages/recipients?q=abc')->assertRedirect();
});
