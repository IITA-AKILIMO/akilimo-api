<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Enums\PriceStatus;
use App\Models\DefaultPrice;
use App\Models\DefaultPriceHistory;
use App\Models\User;
use App\Service\PriceCatalogueService;
use Database\Seeders\DefaultPricesSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

function catalogueAdmin(): User
{
    return User::create([
        'name' => 'Price Admin',
        'username' => 'price_admin_' . str()->random(8),
        'email' => 'price_admin_' . str()->random(8) . '@example.com',
        'password' => bcrypt('password'),
        'role' => EnumUserRole::Admin,
    ]);
}

function catalogueCountry(string $code = 'NG', string $basis = 'open_market'): int
{
    return DB::table('countries')->insertGetId([
        'code' => $code,
        'name' => $code . ' Country',
        'active' => true,
        'default_price_basis' => $basis,
    ]);
}

function catalogueDraft(array $overrides = []): DefaultPrice
{
    return app(PriceCatalogueService::class)->createDraft(array_merge([
        'country' => 'NG',
        'item' => 'urea',
        'price' => 30000,
        'unit' => 'per_bag',
        'bag_kg' => 50,
        'currency' => 'NGN',
        'source' => 'Test source',
        'source_date' => '2026-11-08',
        'confidence' => 'high',
    ], $overrides), catalogueAdmin());
}

it('seeds the real units and bag sizes', function () {
    $this->seed(DefaultPricesSeeder::class);

    expect(DB::table('default_prices')->where('country', 'BI')->where('item', 'FOMIIMBURA')->value('bag_kg'))->toBe(25)
        ->and(DB::table('default_prices')->where('country', 'NG')->where('item', 'urea')->value('bag_kg'))->toBe(50)
        ->and(DB::table('default_prices')->where('country', 'TZ')->where('item', 'cassUP')->value('unit'))->toBe('per_tonne')
        ->and(DB::table('default_prices')->where('country', 'NG')->where('item', 'manual_ploughing')->value('unit'))->toBe('per_acre');
});

it('serves only active, national and effective prices, with the country basis', function () {
    catalogueCountry('NG', 'subsidised');
    $now = now();

    $rows = [
        ['item' => 'urea', 'status' => 'active', 'region' => null, 'effective_from' => null],
        ['item' => 'DAP', 'status' => 'draft', 'region' => null, 'effective_from' => null],
        ['item' => 'MOP', 'status' => 'active', 'region' => null, 'effective_from' => $now->copy()->addDay()->toDateString()],
        ['item' => 'NPK171717', 'status' => 'active', 'region' => 'north', 'effective_from' => null],
    ];

    DB::table('default_prices')->insert(array_map(fn (array $row) => [
        ...$row,
        'country' => 'NG',
        'price' => 30000,
        'unit' => 'per_bag',
        'bag_kg' => 50,
        'currency' => 'NGN',
        'created_at' => $now,
        'updated_at' => $now,
    ], $rows));

    $response = $this->getJson('/api/v1/prices?country=NG')
        ->assertOk()
        ->assertJsonPath('country', 'NG')
        ->assertJsonPath('default_price_basis', 'subsidised')
        ->assertHeader('ETag');

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.item'))->toBe('urea')
        ->and($response->json('data.0.status'))->toBe('active');

    $this->getJson('/api/v1/prices?country=NG', ['If-None-Match' => $response->headers->get('ETag')])
        ->assertStatus(304);
});

it('returns only rows changed after updated_since', function () {
    catalogueCountry('NG');
    $now = now();

    DB::table('default_prices')->insert([
        ['country' => 'NG', 'item' => 'urea', 'price' => 30000, 'unit' => 'per_bag', 'bag_kg' => 50, 'currency' => 'NGN', 'status' => 'active', 'created_at' => $now->copy()->subDays(2), 'updated_at' => $now->copy()->subDays(2)],
        ['country' => 'NG', 'item' => 'DAP', 'price' => 40000, 'unit' => 'per_bag', 'bag_kg' => 50, 'currency' => 'NGN', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
    ]);

    $this->getJson('/api/v1/prices?country=NG&updated_since=' . urlencode($now->copy()->subDay()->toIso8601String()))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.item', 'DAP');
});

it('keeps drafts out of the legacy default-prices endpoint', function () {
    catalogueCountry('NG');
    catalogueDraft();
    DB::table('default_prices')->insert([
        'country' => 'NG', 'item' => 'DAP', 'price' => 40000, 'unit' => 'per_bag', 'bag_kg' => 50,
        'currency' => 'NGN', 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/default-prices?country=NG')->assertOk();
    expect(collect($response->json('data'))->pluck('item')->all())->toBe(['DAP']);
});

it('records history and requires a different admin for a change over 30 percent', function () {
    $first = catalogueAdmin();
    $second = catalogueAdmin();
    $service = app(PriceCatalogueService::class);

    $price = catalogueDraft();
    expect($price->status)->toBe(PriceStatus::Draft);

    $price = $service->activate($price->fresh(), $first);
    expect($price->status)->toBe(PriceStatus::Active);

    $service->saveDraft($price, ['price' => 50000], $first);
    $price = $service->activate($price->fresh(), $first);

    expect($price->status)->toBe(PriceStatus::PendingSecondReview)
        ->and($price->submitted_by)->toBe($first->id);

    expect(fn () => $service->confirm($price->fresh(), $first))
        ->toThrow(ValidationException::class);

    $price = $service->confirm($price->fresh(), $second);
    expect($price->status)->toBe(PriceStatus::Active)
        ->and($price->submitted_by)->toBeNull()
        ->and(DefaultPriceHistory::where('default_price_id', $price->id)->pluck('action')->all())
        ->toBe(['created', 'activated', 'updated', 'second_review_requested', 'confirmed']);
});

it('refuses a duplicate item and will not re-activate a pending change', function () {
    catalogueCountry('NG');
    $first = catalogueAdmin();
    $second = catalogueAdmin();
    $service = app(PriceCatalogueService::class);

    $price = catalogueDraft();
    $service->activate($price->fresh(), $first);

    expect(fn () => catalogueDraft())
        ->toThrow(ValidationException::class);

    $service->saveDraft($price->fresh(), ['price' => 50000], $first);
    $pending = $service->activate($price->fresh(), $first);
    expect($pending->status)->toBe(PriceStatus::PendingSecondReview);

    expect(fn () => $service->activate($pending->fresh(), $second))
        ->toThrow(ValidationException::class);

    expect($service->confirm($pending->fresh(), $second)->status)->toBe(PriceStatus::Active);
});

it('limits review to the assigned country reviewers', function () {
    catalogueCountry('NG');
    $allowed = catalogueAdmin();
    $blocked = catalogueAdmin();
    DB::table('country_reviewers')->insert(['country' => 'NG', 'user_id' => $allowed->id]);

    $service = app(PriceCatalogueService::class);
    $price = catalogueDraft();

    expect(fn () => $service->activate($price->fresh(), $blocked))
        ->toThrow(ValidationException::class);

    $price = $service->activate($price->fresh(), $allowed);
    expect($price->status)->toBe(PriceStatus::Active);
});

it('creates and activates a price through the admin, writing the audit trail', function () {
    $admin = catalogueAdmin();

    $this->actingAs($admin)->post('/admin/default-prices', [
        'country' => 'NG',
        'item' => 'urea',
        'price' => 30000,
        'unit' => 'per_bag',
        'bag_kg' => 50,
        'currency' => 'NGN',
        'source' => 'TFRA notice',
        'source_date' => '2026-11-08',
        'confidence' => 'high',
    ])->assertRedirect('/admin/default-prices');

    $price = DefaultPrice::where('country', 'NG')->where('item', 'urea')->firstOrFail();
    expect($price->status)->toBe(PriceStatus::Draft)
        ->and($price->history()->count())->toBe(1);

    $this->actingAs($admin)->post("/admin/default-prices/{$price->id}/activate")
        ->assertRedirect();

    expect($price->fresh()->status)->toBe(PriceStatus::Active)
        ->and($price->history()->pluck('action')->all())->toBe(['created', 'activated']);
});

it('saves a country basis and assigns reviewers from the admin', function () {
    $admin = catalogueAdmin();
    $id = catalogueCountry('NG');

    $this->actingAs($admin)->put("/admin/countries/{$id}", [
        'code' => 'NG',
        'name' => 'Nigeria',
        'active' => true,
        'default_price_basis' => 'subsidised',
    ])->assertRedirect('/admin/countries');

    expect(DB::table('countries')->where('id', $id)->value('default_price_basis'))->toBe('subsidised');

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Country Reviewer',
        'username' => 'reviewer_' . str()->random(8),
        'email' => 'reviewer_' . str()->random(8) . '@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
        'reviewer_countries' => ['NG'],
    ])->assertRedirect('/admin/users');

    expect(DB::table('country_reviewers')->where('country', 'NG')->count())->toBe(1);
});
