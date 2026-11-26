<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Jobs\PushMessage;
use App\Models\Country;
use App\Models\Message;
use App\Repositories\MessageRepo;
use App\Repositories\PushDeviceRepo;
use App\Service\Fcm\FcmClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

function makeMessage(array $attributes = []): Message
{
    return Message::create([
        'type' => 'announcement',
        'title' => 'Title',
        'body' => 'Body',
        ...$attributes,
    ]);
}

beforeEach(function () {
    Country::query()->where('code', 'TZ')->exists() || Country::create(['code' => 'TZ', 'name' => 'Tanzania', 'active' => true, 'sort_order' => 1]);
});

it('gives a phone the messages for its country and language and those for everyone', function () {
    $all = makeMessage(['title' => 'everyone']);
    $tz = makeMessage(['title' => 'tz', 'country' => 'TZ']);
    $sw = makeMessage(['title' => 'sw', 'language' => 'sw']);
    makeMessage(['title' => 'ng', 'country' => 'NG']);
    makeMessage(['title' => 'rw', 'language' => 'rw']);

    $titles = collect($this->getJson('/api/v1/messages?country=tz&language=SW')->assertOk()->json('data'))->pluck('title');

    expect($titles->sort()->values()->all())->toBe(['everyone', 'sw', 'tz']);
});

it('leaves out expired messages and honours since and limit', function () {
    makeMessage(['title' => 'expired', 'expires_at' => now()->subMinute()]);
    makeMessage(['title' => 'old'])->forceFill(['created_at' => now()->subDays(2)])->save();
    makeMessage(['title' => 'new']);

    $since = $this->getJson('/api/v1/messages?since=' . urlencode(now()->subDay()->toIso8601String()))->assertOk()->json('data');
    expect(collect($since)->pluck('title')->all())->toBe(['new']);

    $this->getJson('/api/v1/messages?limit=1')->assertOk()->assertJsonCount(1, 'data');
});

it('rejects malformed filters', function () {
    $this->getJson('/api/v1/messages?country=TZA')->assertStatus(422);
    $this->getJson('/api/v1/messages?limit=500')->assertStatus(422);
    $this->getJson('/api/v1/messages?since=yesterday-ish')->assertStatus(422);
});

it('does not let anyone without a token send a message', function () {
    $this->postJson('/api/v1/admin/messages', ['type' => 'advice', 'title' => 'T', 'body' => 'B'])->assertUnauthorized();
});

it('stores a message and pushes it to the topics when FCM is configured', function () {
    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($key, $pem);
    config(['fcm.enabled' => true, 'fcm.project_id' => 'p', 'fcm.credentials' => json_encode(['client_email' => 'a@b.c', 'private_key' => $pem])]);
    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 't']),
        'fcm.googleapis.com/*' => Http::response([]),
    ]);

    $response = $this->actingAsApiUser()->postJson('/api/v1/admin/messages', [
        'type' => 'advice', 'title' => 'Bei mpya', 'body' => 'Bei imebadilika', 'country' => 'tz', 'language' => 'SW', 'target' => 'recommendations',
    ])->assertCreated()->assertJsonPath('push', 'queued')->assertJsonPath('data.country', 'TZ')->assertJsonPath('data.language', 'sw');

    $message = Message::findOrFail($response->json('data.id'));
    expect($message->pushed_at)->not->toBeNull()->and($message->created_by)->not->toBeNull();
    Http::assertSent(fn ($request) => str_contains($request->url(), 'messages:send')
        && $request['message']['condition'] === "'country_TZ' in topics && 'lang_sw' in topics"
        && $request['message']['data']['id'] === $message->id
        && $request['message']['data']['target'] === 'recommendations');
});

it('keeps the message when the push is off, has no audience or fails', function () {
    config(['fcm.enabled' => false]);
    $this->actingAsApiUser()->postJson('/api/v1/admin/messages', ['type' => 'reminder', 'title' => 'T', 'body' => 'B', 'country' => 'TZ'])
        ->assertCreated()->assertJsonPath('push', 'disabled');

    $this->postJson('/api/v1/admin/messages', ['type' => 'reminder', 'title' => 'T', 'body' => 'B'])
        ->assertCreated()->assertJsonPath('push', 'no_audience');

    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($key, $pem);
    config(['fcm.enabled' => true, 'fcm.project_id' => 'p', 'fcm.credentials' => json_encode(['client_email' => 'a@b.c', 'private_key' => $pem])]);
    // The push is only queued during the request; FCM failing later in the worker never loses the message
    Http::fake(['*' => Http::response(['error' => 'down'], 500)]);
    Queue::fake();
    $id = $this->postJson('/api/v1/admin/messages', ['type' => 'reminder', 'title' => 'T', 'body' => 'B', 'language' => 'sw'])
        ->assertCreated()->assertJsonPath('push', 'queued')->json('data.id');
    Queue::assertPushed(PushMessage::class, fn (PushMessage $job) => $job->messageId === $id);
    Http::assertNothingSent();

    expect(fn () => (new PushMessage($id))->handle(app(FcmClient::class), app(MessageRepo::class), app(PushDeviceRepo::class)))
        ->toThrow(RuntimeException::class);

    expect(Message::count())->toBe(3)->and(Message::whereNotNull('pushed_at')->count())->toBe(0);
});

it('validates what an admin sends', function () {
    $this->actingAsApiUser();

    $this->postJson('/api/v1/admin/messages', [])->assertStatus(422)->assertJsonValidationErrors(['type', 'title', 'body']);
    $this->postJson('/api/v1/admin/messages', ['type' => 'weird', 'title' => 'T', 'body' => 'B'])->assertJsonValidationErrors(['type']);
    $this->postJson('/api/v1/admin/messages', ['type' => 'advice', 'title' => 'T', 'body' => 'B', 'country' => 'ZZ'])->assertJsonValidationErrors(['country']);
    $this->postJson('/api/v1/admin/messages', ['type' => 'advice', 'title' => 'T', 'body' => 'B', 'target' => 'elsewhere'])->assertJsonValidationErrors(['target']);
    $this->postJson('/api/v1/admin/messages', ['type' => 'advice', 'title' => 'T', 'body' => 'B', 'expires_at' => now()->subDay()->toIso8601String()])->assertJsonValidationErrors(['expires_at']);
});

it('lists every message for an admin, newest first', function () {
    makeMessage(['title' => 'first'])->forceFill(['created_at' => now()->subDay()])->save();
    makeMessage(['title' => 'second']);

    $titles = collect($this->actingAsApiUser()->getJson('/api/v1/admin/messages')->assertOk()->json('data'))->pluck('title')->all();

    expect($titles)->toBe(['second', 'first']);
});
