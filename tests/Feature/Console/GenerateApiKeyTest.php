<?php

use App\Auth\TokenAbility;
use App\Enums\EnumUserRole;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function keyOwner(EnumUserRole $role = EnumUserRole::Partner): User
{
    $user = User::create([
        'name' => 'Key Owner',
        'username' => 'owner_' . str()->random(6),
        'email' => 'owner_' . str()->random(6) . '@example.com',
        'password' => Hash::make('secret123'),
    ]);
    $user->forceFill(['role' => $role])->save();

    return $user;
}

it('creates a key with the role preset and a working raw key', function () {
    $owner = keyOwner();

    $this->artisan('api-key:generate', ['--user' => $owner->username, '--name' => 'R service', '--no-interaction' => true])
        ->expectsOutputToContain('it will NOT be shown again')
        ->assertSuccessful();

    $key = ApiKey::latest('id')->first();
    expect($key->user_id)->toBe($owner->id)
        ->and($key->name)->toBe('R service')
        ->and($key->abilities)->toBe(TokenAbility::PARTNER_ABILITIES)
        ->and($key->is_active)->toBeTrue();
});

it('grants wildcard when asked for "*"', function () {
    $owner = keyOwner(EnumUserRole::User);

    $this->artisan('api-key:generate', ['--user' => $owner->email, '--name' => 'all', '--ability' => ['*'], '--no-interaction' => true])
        ->assertSuccessful();

    expect(ApiKey::latest('id')->first()->abilities)->toBeNull();
});

it('rejects unknown abilities and unknown users', function () {
    $owner = keyOwner();

    $this->artisan('api-key:generate', ['--user' => $owner->id, '--ability' => ['nope'], '--no-interaction' => true])
        ->assertFailed();
    $this->artisan('api-key:generate', ['--user' => 'nobody-here', '--no-interaction' => true])
        ->assertFailed();

    expect(ApiKey::count())->toBe(0);
});

it('rejects a past expiry date', function () {
    $owner = keyOwner();

    $this->artisan('api-key:generate', ['--user' => $owner->id, '--expires' => '2000-01-01', '--no-interaction' => true])
        ->assertFailed();
});

it('asks for the owner, name, abilities and expiry interactively', function () {
    $owner = keyOwner();

    $this->artisan('api-key:generate')
        ->expectsChoice('Key owner', $owner->id, [$owner->id => "Key Owner <{$owner->email}> — partner"])
        ->expectsQuestion('Key name', 'Interactive key')
        ->expectsChoice('Abilities', 'custom', [
            'preset' => 'Role preset (' . implode(', ', TokenAbility::PARTNER_ABILITIES) . ')',
            'custom' => 'Pick abilities',
            'wildcard' => '* Wildcard (everything)',
        ])
        ->expectsQuestion('Abilities to grant' . (windows_os() ? ' (comma-separated numbers)' : ''), [TokenAbility::TRANSLATIONS_READ])
        ->expectsQuestion('Expiry date (optional)', '')
        ->assertSuccessful();

    $key = ApiKey::latest('id')->first();
    expect($key->name)->toBe('Interactive key')
        ->and($key->abilities)->toBe([TokenAbility::TRANSLATIONS_READ])
        ->and($key->expires_at)->toBeNull();
});
