<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Service\Fcm\FcmClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** A service account JSON with a throwaway RSA key. */
function fcmServiceAccountJson(): string
{
    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($key, $pem);

    return json_encode([
        'client_email' => 'push@akilimo-test.iam.gserviceaccount.com',
        'private_key' => $pem,
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ]);
}

beforeEach(function () {
    Cache::flush();
    config(['fcm.enabled' => true, 'fcm.project_id' => 'akilimo-test', 'fcm.credentials' => fcmServiceAccountJson()]);
});

it('targets one topic for a country or a language and a condition for both', function () {
    expect(FcmClient::target('tz', null))->toBe(['topic' => 'country_TZ'])
        ->and(FcmClient::target(null, 'SW'))->toBe(['topic' => 'lang_sw'])
        ->and(FcmClient::target('TZ', 'sw'))->toBe(['condition' => "'country_TZ' in topics && 'lang_sw' in topics"])
        ->and(FcmClient::target(null, null))->toBeNull();
});

it('is configured only with the switch, a project and a readable service account', function () {
    expect((new FcmClient)->isConfigured())->toBeTrue();

    config(['fcm.enabled' => false]);
    expect((new FcmClient)->isConfigured())->toBeFalse();

    config(['fcm.enabled' => true, 'fcm.credentials' => 'does-not-exist.json']);
    expect((new FcmClient)->isConfigured())->toBeFalse();

    config(['fcm.credentials' => fcmServiceAccountJson(), 'fcm.project_id' => null]);
    expect((new FcmClient)->isConfigured())->toBeFalse();
});

it('sends a data-only message to the topic with a bearer token from the service account', function () {
    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 'tok-1']),
        'fcm.googleapis.com/*' => Http::response(['name' => 'projects/akilimo-test/messages/1']),
    ]);

    (new FcmClient)->send(['topic' => 'country_TZ'], ['id' => 'm1', 'type' => 'advice', 'title' => 'T', 'body' => 'B', 'target' => null]);

    Http::assertSent(function (Request $request) {
        if (!str_contains($request->url(), 'oauth2.googleapis.com')) {
            return true;
        }

        return $request['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer' && substr_count($request['assertion'], '.') === 2;
    });
    Http::assertSent(function (Request $request) {
        if (!str_contains($request->url(), 'fcm.googleapis.com')) {
            return false;
        }
        $message = $request['message'];

        return $request->url() === 'https://fcm.googleapis.com/v1/projects/akilimo-test/messages:send'
            && $request->hasHeader('Authorization', 'Bearer tok-1')
            && $message['topic'] === 'country_TZ'
            && $message['data'] === ['id' => 'm1', 'type' => 'advice', 'title' => 'T', 'body' => 'B']
            && !array_key_exists('notification', $message);
    });
});

it('reuses the access token between sends', function () {
    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 'tok-1']),
        'fcm.googleapis.com/*' => Http::response([]),
    ]);

    $client = new FcmClient;
    $client->send(['topic' => 'lang_sw'], ['title' => 'a']);
    $client->send(['topic' => 'lang_sw'], ['title' => 'b']);

    Http::assertSentCount(3);
});

it('throws when FCM refuses the message', function () {
    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 'tok-1']),
        'fcm.googleapis.com/*' => Http::response(['error' => 'bad'], 400),
    ]);

    (new FcmClient)->send(['topic' => 'lang_sw'], ['title' => 'a']);
})->throws(RuntimeException::class, 'FCM send failed (400)');
