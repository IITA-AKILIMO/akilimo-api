<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Enums\PriceStatus;
use App\Models\PriceCatalogue;
use App\Models\PriceCatalogueHistory;
use App\Models\User;
use App\Service\PriceCatalogueService;
use Database\Seeders\PriceCatalogueSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;

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

function catalogueDraft(array $overrides = []): PriceCatalogue
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
    $this->seed(PriceCatalogueSeeder::class);

    expect(DB::table('price_catalogue')->where('country', 'BI')->where('item', 'FOMIIMBURA')->value('bag_kg'))->toBe(25)
        ->and(DB::table('price_catalogue')->where('country', 'NG')->where('item', 'urea')->value('bag_kg'))->toBe(50)
        ->and(DB::table('price_catalogue')->where('country', 'TZ')->where('item', 'cassUP')->value('unit'))->toBe('per_tonne')
        ->and(DB::table('price_catalogue')->where('country', 'NG')->where('item', 'manual_ploughing')->value('unit'))->toBe('per_acre');
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

    DB::table('price_catalogue')->insert(array_map(fn (array $row) => [
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

    DB::table('price_catalogue')->insert([
        ['country' => 'NG', 'item' => 'urea', 'price' => 30000, 'unit' => 'per_bag', 'bag_kg' => 50, 'currency' => 'NGN', 'status' => 'active', 'created_at' => $now->copy()->subDays(2), 'updated_at' => $now->copy()->subDays(2)],
        ['country' => 'NG', 'item' => 'DAP', 'price' => 40000, 'unit' => 'per_bag', 'bag_kg' => 50, 'currency' => 'NGN', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
    ]);

    $this->getJson('/api/v1/prices?country=NG&updated_since=' . urlencode($now->copy()->subDay()->toIso8601String()))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.item', 'DAP');
});

it('keeps drafts out of the price catalogue endpoint', function () {
    catalogueCountry('NG');
    catalogueDraft();
    DB::table('price_catalogue')->insert([
        'country' => 'NG', 'item' => 'DAP', 'price' => 40000, 'unit' => 'per_bag', 'bag_kg' => 50,
        'currency' => 'NGN', 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/price-catalogue?country=NG')->assertOk();
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
        ->and(PriceCatalogueHistory::where('price_catalogue_id', $price->id)->pluck('action')->all())
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
    // the country is picked from the countries table too
    catalogueCountry('NG');
    // currency is picked from the currencies table, so the one the form posts must exist
    seedCurrencies(['country_code' => 'NG', 'country' => 'Nigeria', 'currency_name' => 'Nigerian naira', 'currency_code' => 'NGN', 'currency_symbol' => '₦']);

    $this->actingAs($admin)->post('/admin/price-catalogue', [
        'country' => 'NG',
        'item' => 'urea',
        'price' => 30000,
        'unit' => 'per_bag',
        'bag_kg' => 50,
        'currency' => 'NGN',
        'source' => 'TFRA notice',
        'source_date' => '2026-11-08',
        'confidence' => 'high',
    ])->assertRedirect('/admin/price-catalogue');

    $price = PriceCatalogue::where('country', 'NG')->where('item', 'urea')->firstOrFail();
    expect($price->status)->toBe(PriceStatus::Draft)
        ->and($price->history()->count())->toBe(1);

    $this->actingAs($admin)->post("/admin/price-catalogue/{$price->id}/activate")
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


it('deactivates a price: it goes back to draft, is no longer served and is recorded', function () {
    $admin = catalogueAdmin();
    $price = catalogueDraft();

    $this->actingAs($admin)->post("/admin/price-catalogue/{$price->id}/activate")->assertRedirect();
    expect($price->fresh()->status)->toBe(PriceStatus::Active);

    $this->actingAs($admin)->post("/admin/price-catalogue/{$price->id}/deactivate", ['note' => 'Price withdrawn'])
        ->assertRedirect()->assertSessionHas('success');

    $fresh = $price->fresh();
    expect($fresh->status)->toBe(PriceStatus::Draft)
        ->and($fresh->submitted_by)->toBeNull()
        ->and($price->history()->pluck('action')->all())->toBe(['created', 'activated', 'deactivated']);

    // Deactivating a draft changes nothing and adds no history
    $this->actingAs($admin)->post("/admin/price-catalogue/{$price->id}/deactivate")->assertRedirect();
    expect($price->history()->count())->toBe(3);

    // It can be activated again
    $this->actingAs($admin)->post("/admin/price-catalogue/{$price->id}/activate")->assertRedirect();
    expect($price->fresh()->status)->toBe(PriceStatus::Active);
});

it('keeps price deactivation to admins who may review the country', function () {
    $price = catalogueDraft();
    $this->actingAs(catalogueAdmin())->post("/admin/price-catalogue/{$price->id}/activate");

    $partner = User::create([
        'name' => 'Partner', 'username' => 'partner_' . str()->random(8), 'email' => 'p_' . str()->random(8) . '@example.com',
        'password' => bcrypt('password'), 'role' => EnumUserRole::Partner,
    ]);

    $this->actingAs($partner)->post("/admin/price-catalogue/{$price->id}/deactivate")->assertRedirect();
    expect($price->fresh()->status)->toBe(PriceStatus::Active);
});


it('gives every price a category from its item and filters the Price catalogue by it', function () {
    $admin = catalogueAdmin();

    DB::table('price_items')->updateOrInsert(['item' => 'urea'], ['unit' => 'per_bag', 'category' => 'FERTILIZER']);
    DB::table('price_items')->updateOrInsert(['item' => 'cassUP'], ['unit' => 'per_tonne', 'category' => 'PRODUCE']);
    DB::table('price_items')->updateOrInsert(['item' => 'weeding1'], ['unit' => 'per_acre', 'category' => 'LABOUR']);

    foreach ([['urea', 'per_bag'], ['cassUP', 'per_tonne'], ['weeding1', 'per_acre']] as [$item, $unit]) {
        DB::table('price_catalogue')->insert([
            'country' => 'NG', 'item' => $item, 'price' => 100, 'unit' => $unit, 'currency' => 'NGN',
            'status' => 'draft', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $this->actingAs($admin)->get('/admin/price-catalogue?per_page=100')
        ->assertInertia(fn (Assert $page) => $page
            ->has('categories', DB::table('price_categories')->count())
            ->where('items.data', function ($rows) {
                $byItem = collect($rows)->pluck('category', 'item');

                return $byItem['urea'] === 'FERTILIZER' && $byItem['cassUP'] === 'PRODUCE' && $byItem['weeding1'] === 'LABOUR';
            }));

    $this->actingAs($admin)->get('/admin/price-catalogue?category=LABOUR&per_page=100')
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.category', 'LABOUR')
            ->where('items.data', fn ($rows) => collect($rows)->isNotEmpty()
                && collect($rows)->every(fn ($r) => $r['category'] === 'LABOUR')
                && collect($rows)->pluck('item')->contains('weeding1')));
});

it('classifies a new fertilizer as a fertilizer price item, and any other new item as OTHER', function () {
    app(\App\Service\FertilizerTypeService::class)->create(['name' => 'Nafaka Super', 'weight' => 50]);

    expect(DB::table('price_items')->where('item', 'NafakaSuper')->value('category'))->toBe('FERTILIZER');

    DB::table('price_items')->insert(['item' => 'herbicide', 'unit' => 'per_kg']);
    expect(DB::table('price_items')->where('item', 'herbicide')->value('category'))->toBe('OTHER');
});


it('keeps a deleted price in the history, from the admin and from the API', function () {
    $admin = catalogueAdmin();
    $price = catalogueDraft();
    $id = $price->id;

    $this->actingAs($admin)->delete("/admin/price-catalogue/{$id}")->assertRedirect();

    expect(PriceCatalogue::find($id))->toBeNull();
    $entry = PriceCatalogueHistory::where('country', $price->country)->where('item', $price->item)->where('action', 'deleted')->first();
    expect($entry)->not->toBeNull()
        ->and($entry->price_catalogue_id)->toBeNull()
        ->and($entry->old_values['price'])->toEqual($price->price)
        ->and($entry->changed_by)->toBe($admin->id);
});

it('refuses a price for a country that does not exist', function () {
    $admin = catalogueAdmin();
    catalogueCountry('NG');

    $this->actingAs($admin)->post('/admin/price-catalogue', [
        'country' => 'ST', 'item' => 'urea', 'price' => 100, 'unit' => 'per_bag', 'currency' => 'NGN',
    ])->assertSessionHasErrors('country');
});


it('has a model named for its table, with the history linked through price_catalogue_id', function () {
    $price = catalogueDraft();

    expect((new \App\Models\PriceCatalogue())->getTable())->toBe('price_catalogue')
        ->and($price)->toBeInstanceOf(\App\Models\PriceCatalogue::class)
        ->and($price->history()->count())->toBe(1)
        ->and($price->history->first()->priceCatalogue->is($price))->toBeTrue();
});
