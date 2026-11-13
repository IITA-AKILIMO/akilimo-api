<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\TrafficExclusion;
use App\Models\User;
use App\Service\TrafficExclusionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(\Database\Seeders\TrafficExclusionSeeder::class);
    $this->seed(\Database\Seeders\FertilizerSeeder::class);
});

function exclusionUser(EnumUserRole $role): User
{
    return User::create([
        'name' => ucfirst($role->value) . ' User',
        'username' => $role->value . '_' . Str::random(6),
        'email' => $role->value . '_' . Str::random(6) . '@example.com',
        'password' => Hash::make('secret123'),
        'role' => $role,
    ]);
}

/** A request in the current app format (user_info); returns its id. */
function newFormatRequest(array $userInfo, ?string $deviceToken = null): int
{
    $id = DB::table('api_requests')->insertGetId([
        'request_id' => (string)Str::uuid(),
        'device_token' => $deviceToken ?? (string)Str::uuid(),
        'droid_request' => json_encode(['user_info' => array_merge([
            'user_name' => 'farmer1', 'first_name' => 'Ada', 'last_name' => 'Obi', 'gender' => 'F', 'phone_number' => null,
        ], $userInfo)]),
        'plumber_request' => json_encode(['country' => 'NG', 'FR' => true]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    app(TrafficExclusionService::class)->refresh($id);

    return $id;
}

function isExcluded(int $id): bool
{
    return (bool)DB::table('api_requests')->where('id', $id)->value('excluded');
}

it('seeds the old staff rules', function () {
    expect(TrafficExclusion::active()->orderBy('id')->get(['field', 'match_type', 'value'])->toArray())->toBe([
        ['field' => 'phone', 'match_type' => 'starts_with', 'value' => '254'],
        ['field' => 'phone', 'match_type' => 'starts_with', 'value' => '49'],
        ['field' => 'name', 'match_type' => 'contains', 'value' => 'KREYE'],
        ['field' => 'name', 'match_type' => 'contains', 'value' => 'C K'],
    ]);
});

it('excludes new-format requests and fills their name, phone and gender columns', function () {
    $staff = newFormatRequest(['phone_number' => '+254 700 000 000']);
    $farmer = newFormatRequest(['phone_number' => '0803 000 0000', 'gender' => 'M']);

    expect(isExcluded($staff))->toBeTrue()
        ->and(isExcluded($farmer))->toBeFalse();

    $row = DB::table('api_requests')->where('id', $farmer)->first(['full_names', 'phone_number', 'gender', 'gender_code']);
    expect($row->full_names)->toBe('Ada Obi')
        ->and($row->phone_number)->toBe('0803 000 0000')
        ->and($row->gender)->toBe('M')
        ->and($row->gender_code)->toBe('M');
});

it('matches exact numbers, name patterns and device IDs', function () {
    TrafficExclusion::query()->update(['active' => false]);
    TrafficExclusion::create(['field' => 'phone', 'match_type' => 'exact', 'value' => '+234 803 111 2222']);
    TrafficExclusion::create(['field' => 'name', 'match_type' => 'regex', 'value' => '^qa[0-9]+$']);
    TrafficExclusion::create(['field' => 'device', 'match_type' => 'starts_with', 'value' => 'EMULATOR-']);

    $exact = newFormatRequest(['phone_number' => '2348031112222']);
    $otherNumber = newFormatRequest(['phone_number' => '2348031112223']);
    $pattern = newFormatRequest(['user_name' => 'QA17']);
    $device = newFormatRequest([], 'emulator-5554');
    $farmer = newFormatRequest(['user_name' => 'qa-farmer']);

    expect(isExcluded($exact))->toBeTrue()
        ->and(isExcluded($otherNumber))->toBeFalse()
        ->and(isExcluded($pattern))->toBeTrue()
        ->and(isExcluded($device))->toBeTrue()
        ->and(isExcluded($farmer))->toBeFalse();
});

it('flags new requests as they are logged', function () {
    TrafficExclusion::create(['field' => 'name', 'match_type' => 'exact', 'value' => 'akilimo-mcp']);
    $this->actingAsApiUser();
    Http::fake(['*' => Http::response(plumberSuccessResponse())]);

    $payload = validComputePayload();
    $payload['user_info']['user_name'] = 'akilimo-mcp';
    $this->postJson('/api/v1/recommendations/compute', $payload)->assertOk();

    expect((bool)DB::table('api_requests')->latest('id')->value('excluded'))->toBeTrue();
});

it('recomputes every request when an admin switches a rule off', function () {
    $kenyan = newFormatRequest(['phone_number' => '254711000000']);
    expect(isExcluded($kenyan))->toBeTrue();

    $rule = TrafficExclusion::where('value', '254')->firstOrFail();

    $this->actingAs(exclusionUser(EnumUserRole::Admin))
        ->patch("/admin/traffic-exclusions/{$rule->id}", ['active' => false])
        ->assertRedirect('/admin/traffic-exclusions')
        ->assertSessionHas('success', 'Rule updated. 1 request changed.');

    expect(isExcluded($kenyan))->toBeFalse();
});

it('lists rules with how many requests each matches', function () {
    newFormatRequest(['phone_number' => '254711000000']);

    $this->actingAs(exclusionUser(EnumUserRole::Admin))
        ->get('/admin/traffic-exclusions')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TrafficExclusions/Index')
            ->has('rules', 4)
            ->where('rules.0.value', '254')
            ->where('rules.0.matches', 1)
            ->where('totals.excluded', 1)
        );
});

it('refuses an invalid pattern and a phone rule without digits', function () {
    $admin = exclusionUser(EnumUserRole::Admin);

    $this->actingAs($admin)
        ->post('/admin/traffic-exclusions', ['field' => 'name', 'match_type' => 'regex', 'value' => '(unclosed'])
        ->assertSessionHasErrors('value');

    $this->actingAs($admin)
        ->post('/admin/traffic-exclusions', ['field' => 'phone', 'match_type' => 'starts_with', 'value' => 'abc'])
        ->assertSessionHasErrors('value');

    expect(TrafficExclusion::count())->toBe(4);
});

it('keeps partners out of the exclusion rules', function () {
    // The role middleware sends them back with an error rather than a bare 403
    $this->actingAs(exclusionUser(EnumUserRole::Partner))
        ->get('/admin/traffic-exclusions')
        ->assertRedirect()
        ->assertSessionHasErrors();

    $this->actingAs(exclusionUser(EnumUserRole::Partner))
        ->post('/admin/traffic-exclusions', ['field' => 'phone', 'match_type' => 'exact', 'value' => '123'])
        ->assertRedirect();

    expect(TrafficExclusion::count())->toBe(4);
});
