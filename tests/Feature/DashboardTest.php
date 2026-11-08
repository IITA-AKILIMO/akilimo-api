<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\User;
use App\Service\TrafficExclusionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->withoutVite());

function dashboardUser(EnumUserRole $role): User
{
    return User::create([
        'name' => ucfirst($role->value) . ' User',
        'username' => $role->value . '_' . Str::random(6),
        'email' => $role->value . '_' . Str::random(6) . '@example.com',
        'password' => Hash::make('secret123'),
        'role' => $role,
    ]);
}

/**
 * One api_requests row as RecommendationService writes it. country_code,
 * use_case and gender are generated columns, computed from the stored
 * request JSON, so they are set through `country` and the JSON; `excluded`
 * comes from the traffic exclusion rules, applied as the service does.
 */
function loggedRequest(array $overrides = [], string $country = 'NG'): void
{
    $id = DB::table('api_requests')->insertGetId(array_merge([
        'request_id' => (string)Str::uuid(),
        'device_token' => Str::random(12),
        'droid_request' => json_encode(['user_info' => ['gender' => 'F', 'user_name' => 'farmer']]),
        'plumber_request' => json_encode(['country' => $country, 'FR' => true]),
        'plumber_response' => json_encode(['status' => 'success', 'data' => []]),
        'request_duration_ms' => 1000,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ], $overrides));

    app(TrafficExclusionService::class)->refresh($id);
}

// The old app's userInfo with a staff phone number (seeded rule: starts with 254).
function staffDroidRequest(): string
{
    return json_encode(['userInfo' => ['mobileNumber' => '254700000000', 'userName' => 'staff']]);
}

it('reports response time, success rate and reach from logged requests', function () {
    loggedRequest(['request_duration_ms' => 1000, 'device_token' => 'device-a']);
    loggedRequest(['request_duration_ms' => 3000, 'device_token' => 'device-a']);
    loggedRequest(['request_duration_ms' => 2000, 'device_token' => 'device-b'], 'TZ');
    // A failure: counted in the success rate, left out of the response time
    loggedRequest([
        'request_duration_ms' => 30000,
        'device_token' => 'device-c',
        'plumber_response' => json_encode(['error' => 'Connection failed', 'message' => 'Unable to connect']),
    ]);
    // Nothing saved yet: neither success nor failure
    loggedRequest(['plumber_response' => '{}', 'request_duration_ms' => null, 'device_token' => 'device-d']);
    // Staff traffic is left out of everything but its own count
    loggedRequest(['droid_request' => staffDroidRequest(), 'device_token' => 'staff', 'request_duration_ms' => 50]);

    $this->actingAs(dashboardUser(EnumUserRole::Admin))
        ->get('/admin?metrics_days=7&chart_days=7')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('metrics.requestsLastDays', 5)
            ->where('metrics.staffRequests', 1)
            ->where('metrics.answeredRequests', 4)
            ->where('metrics.failedRequests', 1)
            ->where('metrics.unansweredRequests', 1)
            ->where('metrics.successRate', 75)
            ->where('metrics.medianResponseMs', 2000)
            ->where('metrics.p95ResponseMs', 3000)
            ->where('metrics.timedRequests', 3)
            ->where('metrics.uniqueDevices', 4)
            ->where('charts.byCountry.0', ['country_code' => 'NG', 'count' => 4])
            ->where('charts.byCountry.1', ['country_code' => 'TZ', 'count' => 1])
            ->where('failures.0.message', 'Connection failed: Unable to connect')
        );
});

it('groups gender from the column and the request, and ignores the MCP placeholder', function () {
    // Old app: gender in userInfo (the generated `gender` column), in Swahili
    loggedRequest(['droid_request' => json_encode(['userInfo' => ['gender' => 'Mwanamke']])]);
    loggedRequest(['droid_request' => json_encode(['user_info' => ['gender' => 'M', 'user_name' => 'farmer']])]);
    loggedRequest(['droid_request' => json_encode(['user_info' => ['gender' => 'M', 'user_name' => 'akilimo-mcp']])]);
    loggedRequest(['droid_request' => '{}']);

    $this->actingAs(dashboardUser(EnumUserRole::Admin))
        ->get('/admin?chart_days=7')
        ->assertInertia(fn (Assert $page) => $page
            ->where('charts.byGender', ['female' => 1, 'male' => 1, 'not_recorded' => 2])
        );
});

it('averages feedback satisfaction over real 1 to 5 answers only', function () {
    foreach ([5, 4, 0] as $rating) {
        DB::table('user_feedback')->insert([
            'akilimo_usage' => 'fertilizer recommendation',
            'user_type' => 'farmer',
            'akilimo_rec_rating' => $rating,
            'akilimo_useful_rating' => 8,
            'language' => 'en',
            'device_token' => Str::random(12),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->actingAs(dashboardUser(EnumUserRole::Admin))
        ->get('/admin')
        ->assertInertia(fn (Assert $page) => $page
            ->where('feedback.responses', 3)
            ->where('feedback.ratedResponses', 2)
            ->where('feedback.avgSatisfaction', 4.5)
            ->where('feedback.distribution.5', 1)
        );
});

it('flags price lists that have not been updated recently', function () {
    $this->actingAs(dashboardUser(EnumUserRole::Admin))
        ->get('/admin')
        ->assertInertia(fn (Assert $page) => $page
            ->where('freshness.staleAfterDays', 90)
            ->has('freshness.prices')
            // An empty list has no date, so nobody can tell how old it is
            ->where('freshness.prices.0.last_updated', null)
            ->where('freshness.prices.0.stale', true)
        );
});

it('does not send partners the failures or the data freshness', function () {
    loggedRequest(['plumber_response' => json_encode(['error' => 'Internal error'])]);

    $this->actingAs(dashboardUser(EnumUserRole::Partner))
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('metrics')
            ->has('feedback')
            ->missing('failures')
            ->missing('freshness')
        );
});
