<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Models\AppVersion;

function storeVersion(array $attributes = []): AppVersion
{
    // The migration stored the starting row
    $version = AppVersion::where('platform', 'android')->firstOrFail();
    $version->update($attributes);

    return $version;
}

it('answers publicly with the stored minimum and latest version code of android', function () {
    storeVersion(['min_supported' => 12, 'latest' => 20, 'message' => 'Please update', 'store_url' => 'https://play.google.com/x']);

    $this->getJson('/api/v1/app/version')
        ->assertOk()
        ->assertJsonPath('data.android.min_supported', 12)
        ->assertJsonPath('data.android.latest', 20)
        ->assertJsonPath('data.android.message', 'Please update')
        ->assertJsonPath('data.android.store_url', 'https://play.google.com/x');
});

it('answers with the starting row the migration stored', function () {
    $this->getJson('/api/v1/app/version')
        ->assertOk()
        ->assertJsonPath('data.android.min_supported', 1)
        ->assertJsonPath('data.android.latest', 1)
        ->assertJsonPath('data.android.message', null)
        ->assertJsonPath('data.android.store_url', 'https://play.google.com/store/apps/details?id=com.akilimo.mobile');
});

it('is cacheable', function () {
    expect($this->getJson('/api/v1/app/version')->assertOk()->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=300');
});

it('never reports a latest version below the minimum', function () {
    storeVersion(['min_supported' => 30, 'latest' => 10]);

    $this->getJson('/api/v1/app/version')->assertOk()->assertJsonPath('data.android.latest', 30);
});

it('leaves a blank message and store url as null', function () {
    storeVersion(['message' => '  ', 'store_url' => '']);

    $this->getJson('/api/v1/app/version')
        ->assertOk()
        ->assertJsonPath('data.android.message', null)
        ->assertJsonPath('data.android.store_url', null);
});

it('filters by platform and rejects an unknown one', function () {
    $this->getJson('/api/v1/app/version?platform=android')->assertOk()->assertJsonStructure(['data' => ['android']]);
    $this->getJson('/api/v1/app/version?platform=windows')->assertStatus(422);
});

it('lets an admin store the version gate, which then overrides the defaults', function () {
    $this->actingAsApiUser()
        ->putJson('/api/v1/admin/app/version', ['min_supported' => 40, 'latest' => 45, 'message' => 'Update now'])
        ->assertOk()
        ->assertJsonPath('data.android.min_supported', 40);

    $this->getJson('/api/v1/app/version')
        ->assertOk()
        ->assertJsonPath('data.android.min_supported', 40)
        ->assertJsonPath('data.android.latest', 45)
        ->assertJsonPath('data.android.message', 'Update now');
});

it('keeps the other fields when only latest is sent, as the publish workflow does', function () {
    $this->actingAsApiUser();
    $this->putJson('/api/v1/admin/app/version', ['min_supported' => 40, 'latest' => 45, 'message' => 'Hi'])->assertOk();
    $this->putJson('/api/v1/admin/app/version', ['latest' => 50])->assertOk();

    $this->getJson('/api/v1/app/version')
        ->assertJsonPath('data.android.min_supported', 40)
        ->assertJsonPath('data.android.latest', 50)
        ->assertJsonPath('data.android.message', 'Hi');
});

it('raises latest to the minimum when the minimum is set above it', function () {
    $this->actingAsApiUser()
        ->putJson('/api/v1/admin/app/version', ['min_supported' => 60, 'latest' => 10])
        ->assertOk()
        ->assertJsonPath('data.android.latest', 60);
});

it('refuses to change the version gate without a token and rejects bad values', function () {
    $this->putJson('/api/v1/admin/app/version', ['latest' => 5])->assertUnauthorized();

    $this->actingAsApiUser()
        ->putJson('/api/v1/admin/app/version', ['latest' => 0, 'store_url' => 'nope'])
        ->assertStatus(422);
});
