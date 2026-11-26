<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Enums\PriceStatus;
use App\Jobs\PushMessage;
use App\Jobs\SendChangeNotice;
use App\Models\AppVersion;
use App\Models\Country;
use App\Models\CountryFertilizer;
use App\Models\FertilizerType;
use App\Models\Message;
use App\Models\PriceCatalogue;
use App\Models\PushDevice;
use App\Models\StarchFactory;
use App\Models\User;
use App\Repositories\MessageRepo;
use App\Repositories\PushDeviceRepo;
use App\Service\FarmerNotifier;
use App\Service\Fcm\FcmClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

function noticeFcmOn(): void
{
    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($key, $pem);
    config(['fcm.enabled' => true, 'fcm.project_id' => 'p', 'fcm.credentials' => json_encode(['client_email' => 'a@b.c', 'private_key' => $pem])]);
    cache()->forget('fcm.access_token');
}

function noticeDevice(array $attributes = []): PushDevice
{
    return PushDevice::create(['device_token' => 'inst-' . Str::random(10), 'fcm_token' => 'fcm-' . Str::random(16), ...$attributes]);
}

beforeEach(function () {
    // Console runs (these tests) never notify unless switched on, as for seeders and commands
    config(['farmer-notifications.in_console' => true]);
    Country::query()->where('code', 'TZ')->exists() || Country::create(['code' => 'TZ', 'name' => 'Tanzania', 'active' => true, 'sort_order' => 1]);
});

// ── What queues a notice ─────────────────────────────────────────────────────

it('queues a delayed notice when a price farmers are served changes, but not for a draft', function () {
    Queue::fake();

    PriceCatalogue::create(['country' => 'TZ', 'item' => 'urea', 'price' => 30000, 'unit' => 'per_bag', 'currency' => 'TZS', 'status' => PriceStatus::Draft]);
    Queue::assertNothingPushed();

    $price = PriceCatalogue::create(['country' => 'TZ', 'item' => 'dap', 'price' => 40000, 'unit' => 'per_bag', 'currency' => 'TZS', 'status' => PriceStatus::Active]);
    Queue::assertPushed(SendChangeNotice::class, fn (SendChangeNotice $j) => $j->kind === 'prices' && $j->country === 'TZ' && $j->delay !== null);

    // Another edit before the notice runs joins it (the queue holds one notice per kind and country)
    $price->update(['price' => 41000]);
    Queue::assertPushed(SendChangeNotice::class, 1);

    PriceCatalogue::create(['country' => 'NG', 'item' => 'dap', 'price' => 40000, 'unit' => 'per_bag', 'currency' => 'NGN', 'status' => PriceStatus::Active]);
    Queue::assertPushed(SendChangeNotice::class, 2);

    $price->update(['source' => 'a note']); // nothing farmers see
    Queue::assertPushed(SendChangeNotice::class, 2);
});

it('queues notices for fertilizer, starch and app version changes', function () {
    Queue::fake();
    FertilizerType::create(['type' => 'UREA', 'label' => 'Urea', 'name' => 'Urea', 'weight' => 50, 'category' => 'STRAIGHT']);

    CountryFertilizer::create(['fertilizer_type' => 'UREA', 'country' => 'TZ', 'use_case' => 'FR', 'available' => true]);
    Queue::assertPushed(SendChangeNotice::class, fn (SendChangeNotice $j) => $j->kind === 'fertilizers' && $j->country === 'TZ');

    StarchFactory::create(['factory_name' => 'F1', 'factory_label' => 'f1', 'country' => 'TZ', 'factory_active' => true]);
    Queue::assertPushed(SendChangeNotice::class, fn (SendChangeNotice $j) => $j->kind === 'starch' && $j->country === 'TZ');

    $version = AppVersion::query()->updateOrCreate(['platform' => 'android'], ['min_supported' => 10, 'latest' => 10]);
    $version->update(['latest' => 12]);
    Queue::assertPushed(SendChangeNotice::class, fn (SendChangeNotice $j) => $j->kind === 'app_update' && $j->country === null);

    $version->update(['min_supported' => 11]);
    Queue::assertPushed(SendChangeNotice::class, fn (SendChangeNotice $j) => $j->kind === 'app_forced_update');
});

it('stays quiet for console runs unless switched on, and when switched off', function () {
    Queue::fake();

    FertilizerType::create(['type' => 'UREA', 'label' => 'Urea', 'name' => 'Urea', 'weight' => 50, 'category' => 'STRAIGHT']);
    FertilizerType::create(['type' => 'DAP', 'label' => 'DAP', 'name' => 'DAP', 'weight' => 50, 'category' => 'COMPOUND']);
    config(['farmer-notifications.in_console' => false]);
    CountryFertilizer::create(['fertilizer_type' => 'UREA', 'country' => 'TZ', 'use_case' => 'FR', 'available' => true]);

    config(['farmer-notifications.in_console' => true, 'farmer-notifications.enabled' => false]);
    CountryFertilizer::create(['fertilizer_type' => 'DAP', 'country' => 'TZ', 'use_case' => 'FR', 'available' => true]);

    Queue::assertNothingPushed();
});

it('de-duplicates by kind and country so a batch of edits is one notice', function () {
    expect((new SendChangeNotice('prices', 'TZ'))->uniqueId())->toBe((new SendChangeNotice('prices', 'TZ'))->uniqueId())
        ->and((new SendChangeNotice('prices', 'TZ'))->uniqueId())->not->toBe((new SendChangeNotice('prices', 'NG'))->uniqueId())
        ->and((new SendChangeNotice('starch', 'TZ'))->uniqueId())->not->toBe((new SendChangeNotice('prices', 'TZ'))->uniqueId());
});

// ── The notice job ───────────────────────────────────────────────────────────

it('stores one message per language with the country name and queues each push', function () {
    noticeFcmOn();
    Queue::fake();

    (new SendChangeNotice('prices', 'TZ'))->handle(app(FarmerNotifier::class));

    $messages = Message::query()->orderBy('language')->get();
    expect($messages->pluck('language')->all())->toBe(['en', 'rw', 'sw'])
        ->and($messages->every(fn (Message $m) => $m->country === 'TZ' && $m->expires_at !== null && $m->type->value === 'advice'))->toBeTrue()
        ->and($messages->firstWhere('language', 'en')->title)->toBe('New prices for Tanzania')
        ->and($messages->firstWhere('language', 'sw')->title)->toBe('Bei mpya kwa Tanzania');
    Queue::assertPushed(PushMessage::class, 3);
    Http::assertNothingSent();
});

it('sends app update notices to every country, per language', function () {
    noticeFcmOn();
    Queue::fake();

    (new SendChangeNotice('app_forced_update', null))->handle(app(FarmerNotifier::class));

    expect(Message::whereNull('country')->count())->toBe(3)->and(Message::first()->type->value)->toBe('announcement');
    Queue::assertPushed(PushMessage::class, 3);
});

// ── The push job: the only thing that talks to FCM ───────────────────────────

it('pushes a topic message, marks it pushed and skips an expired one', function () {
    noticeFcmOn();
    Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'at'], 200), 'fcm.googleapis.com/*' => Http::response(['name' => 'ok'], 200)]);
    $live = Message::create(['type' => 'advice', 'title' => 'T', 'body' => 'B', 'country' => 'TZ', 'language' => 'sw']);
    $old = Message::create(['type' => 'advice', 'title' => 'T', 'body' => 'B', 'country' => 'TZ', 'expires_at' => now()->subMinute()]);

    foreach ([$live, $old] as $message) {
        (new PushMessage($message->id))->handle(app(FcmClient::class), app(MessageRepo::class), app(PushDeviceRepo::class));
    }

    expect($live->fresh()->pushed_at)->not->toBeNull()->and($old->fresh()->pushed_at)->toBeNull();
    Http::assertSent(fn ($r) => str_contains($r->url(), 'messages:send') && $r['message']['condition'] === "'country_TZ' in topics && 'lang_sw' in topics");
});

it('pushes to one phone and skips a phone that has gone', function () {
    noticeFcmOn();
    Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'at'], 200), 'fcm.googleapis.com/*' => Http::response(['name' => 'ok'], 200)]);
    $device = noticeDevice();
    $message = Message::create(['type' => 'advice', 'title' => 'T', 'body' => 'B', 'recipient_device_token' => $device->device_token]);
    $run = fn (int $deviceId) => (new PushMessage($message->id, $deviceId))->handle(app(FcmClient::class), app(MessageRepo::class), app(PushDeviceRepo::class));

    $run($device->id + 1000);
    expect($message->fresh()->pushed_at)->toBeNull();

    $run($device->id);
    expect($message->fresh()->pushed_at)->not->toBeNull();
    Http::assertSent(fn ($r) => str_contains($r->url(), 'messages:send') && ($r['message']['token'] ?? null) === $device->fcm_token);
});

it('does nothing in the push job when FCM is off', function () {
    config(['fcm.enabled' => false]);
    Http::fake();
    $message = Message::create(['type' => 'advice', 'title' => 'T', 'body' => 'B', 'country' => 'TZ']);

    (new PushMessage($message->id))->handle(app(FcmClient::class), app(MessageRepo::class), app(PushDeviceRepo::class));

    Http::assertNothingSent();
});

// ── Consent update ───────────────────────────────────────────────────────────

it('notifies farmers on an older consent version once, in their phone language', function () {
    noticeFcmOn();
    Queue::fake();
    config(['farmer-accounts.consent_version' => 'v2']);
    $make = fn (string $version, ?string $language) => tap(User::create([
        'name' => 'F', 'username' => 'f_' . Str::random(8), 'email' => 'f_' . Str::random(8) . '@example.com', 'password' => bcrypt('x'),
        'role' => EnumUserRole::User, 'consent_at' => now(), 'consent_version' => $version,
    ]), fn (User $u) => noticeDevice(['user_id' => $u->id, 'language' => $language]));
    $make('v1', 'sw');
    $make('v1', null);
    $make('v2', 'sw'); // already on the current version

    $this->artisan('farmers:notify-consent', ['--dry-run' => true])->expectsOutputToContain('2 farmer(s) would be notified')->assertSuccessful();
    Queue::assertNothingPushed();

    $this->artisan('farmers:notify-consent')->expectsOutputToContain('2 farmer(s) notified')->assertSuccessful();
    expect(Message::whereNotNull('recipient_user_id')->pluck('title')->sort()->values()->all())
        ->toBe(['Our privacy policy changed', 'Sera yetu ya faragha imebadilika']);
    Queue::assertPushed(PushMessage::class, 2);

    $this->artisan('farmers:notify-consent')->expectsOutputToContain('0 farmer(s) notified')->assertSuccessful();
    Cache::flush();
});
