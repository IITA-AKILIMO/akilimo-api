<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->withoutVite());

function versionsAdmin(EnumUserRole $role = EnumUserRole::Admin): User
{
    return User::create([
        'name' => ucfirst($role->value) . ' Versions',
        'username' => $role->value . '_' . Str::random(6),
        'email' => $role->value . '_' . Str::random(6) . '@example.com',
        'password' => Hash::make('secret123'),
        'role' => $role,
    ]);
}

it('shows the stored versions of each platform', function () {
    $this->actingAs(versionsAdmin())->get('/admin/app-versions')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('AppVersions/Index')
            ->has('versions', 2)
            ->where('versions.0.platform', 'android')
            ->where('versions.0.min_supported', 1));
});

it('saves the versions an admin sets, and clears a blank message and store link', function () {
    $admin = versionsAdmin();

    $this->actingAs($admin)->put('/admin/app-versions/android', [
        'min_supported' => 30, 'latest' => 42, 'message' => 'Please update', 'store_url' => 'https://example.com/app',
    ])->assertRedirect('/admin/app-versions');

    $this->getJson('/api/v1/app/version?platform=android')->assertOk()
        ->assertJsonPath('data.android', [
            'min_supported' => 30, 'latest' => 42, 'message' => 'Please update', 'store_url' => 'https://example.com/app',
        ]);

    $this->actingAs($admin)->put('/admin/app-versions/android', [
        'min_supported' => 30, 'latest' => 42, 'message' => '', 'store_url' => '',
    ])->assertRedirect('/admin/app-versions');

    $this->getJson('/api/v1/app/version?platform=android')
        ->assertJsonPath('data.android.message', null)
        ->assertJsonPath('data.android.store_url', null);
});

it('never keeps the newest version below the lowest allowed one', function () {
    $this->actingAs(versionsAdmin())->put('/admin/app-versions/android', ['min_supported' => 50, 'latest' => 10])
        ->assertRedirect();

    $this->getJson('/api/v1/app/version?platform=android')
        ->assertJsonPath('data.android.min_supported', 50)
        ->assertJsonPath('data.android.latest', 50);
});

it('rejects bad values and an unknown platform', function () {
    $admin = versionsAdmin();

    $this->actingAs($admin)->put('/admin/app-versions/android', ['min_supported' => 0, 'latest' => 'x', 'store_url' => 'nope'])
        ->assertSessionHasErrors(['min_supported', 'latest', 'store_url']);

    $this->actingAs($admin)->put('/admin/app-versions/windows', ['min_supported' => 1, 'latest' => 1])
        ->assertNotFound();
});

it('is closed to partners and guests', function () {
    $this->put('/admin/app-versions/android', ['min_supported' => 1, 'latest' => 1])->assertRedirect();

    $partner = versionsAdmin(EnumUserRole::Partner);
    // The role middleware sends a partner back with a message rather than answering 403
    $this->actingAs($partner)->get('/admin/app-versions')->assertRedirect();
    $this->actingAs($partner)->put('/admin/app-versions/android', ['min_supported' => 99, 'latest' => 99])->assertRedirect();

    $this->getJson('/api/v1/app/version?platform=android')->assertJsonPath('data.android.min_supported', 1);
});
