<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Models\FertilizerPrice;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function viewCountry(string $code, string $basis = 'open_market'): void
{
    DB::table('countries')->insert([
        'code' => $code,
        'name' => $code . ' Country',
        'active' => true,
        'default_price_basis' => $basis,
    ]);
}

/**
 * Sell a fertiliser in a country. The key is the type and the country (UREA_TZ),
 * so the type is the key without its country suffix.
 */
function viewFertilizer(string $country, string $label, string $key): int
{
    $type = preg_replace('/_' . preg_quote($country, '/') . '$/', '', $key);

    if (! DB::table('fertilizer_types')->where('type', $type)->exists()) {
        DB::table('fertilizer_types')->insert(['type' => $type, 'label' => $label, 'name' => $label, 'weight' => 50]);
    }

    return DB::table('country_fertilizers')->insertGetId([
        'fertilizer_type' => $type,
        'country' => $country,
        'use_case' => 'FR',
        'cis' => 1,
        'cim' => 1,
        'available' => 1,
        'sort_order' => 1,
    ]);
}

function viewPrice(array $overrides = []): int
{
    return DB::table('default_prices')->insertGetId(array_merge([
        'country' => 'TZ',
        'item' => 'urea',
        'fertilizer_type' => 'UREA',
        'price' => 50000,
        'subsidised_price' => null,
        'unit' => 'per_bag',
        'bag_kg' => 50,
        'currency' => 'TZS',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

/** The option rows of one fertiliser, cheapest first. */
function viewOptions(string $key): array
{
    return DB::table('fertilizer_prices')->where('fertilizer_key', $key)->where('price_per_bag', '>', 0)
        ->orderBy('sort_order')->pluck('price_per_bag')->map(fn ($p) => (float) $p)->all();
}

it('serves four rounded options at -30, -10, +10 and +30 percent', function () {
    viewCountry('TZ');
    viewFertilizer('TZ', 'urea', 'UREA_TZ');
    viewPrice();

    expect(viewOptions('UREA_TZ'))->toBe([35000.0, 45000.0, 55000.0, 65000.0]);
});

it('shows the farmer prices, never percentages, in any list the app reads', function () {
    viewCountry('TZ');
    viewFertilizer('TZ', 'urea', 'UREA_TZ');
    DB::table('currencies')->insert(['country_code' => 'TZ', 'country' => 'Tanzania', 'currency_name' => 'Tanzanian shilling', 'currency_code' => 'TZS', 'currency_symbol' => 'TSh']);
    viewPrice();
    foreach ([
        ['cassUP', 'per_tonne', 180000], ['maize_grain', 'per_kg', 230], ['maize_freshCob', 'per_cob', 50],
        ['sweetPotato_tuber', 'per_tonne', 120000], ['investment_amount', 'per_acre', 57550], ['manual_ploughing', 'per_acre', 175000],
    ] as [$item, $unit, $price]) {
        viewPrice(['item' => $item, 'fertilizer_type' => null, 'unit' => $unit, 'price' => $price]);
    }

    foreach (['fertilizer-prices', 'cassava-prices', 'maize-prices', 'potato-prices', 'investment-amounts', 'operation-costs'] as $list) {
        $body = $this->getJson("/api/v1/{$list}/country/TZ?per_page=100");

        $body->assertOk();
        expect(str_contains($body->getContent(), '%'))->toBeFalse("{$list} shows a percentage");
    }

    // the fertiliser choices carry no description; only the two markers are labelled, as before
    expect(DB::table('fertilizer_prices')->where('price_per_bag', '>', 0)->pluck('desc')->unique()->values()->all())->toBe(['']);
});

it('labels every choice in words the client shows, and the zero and negative rows as text', function () {
    viewCountry('TZ');
    viewFertilizer('TZ', 'urea', 'UREA_TZ');
    DB::table('currencies')->insert(['country_code' => 'TZ', 'country' => 'Tanzania', 'currency_name' => 'Tanzanian shilling', 'currency_code' => 'TZS', 'currency_symbol' => 'TSh']);
    viewPrice();
    viewPrice(['item' => 'cassUP', 'fertilizer_type' => null, 'unit' => 'per_tonne', 'price' => 180000]);
    viewPrice(['item' => 'investment_amount', 'fertilizer_type' => null, 'unit' => 'per_acre', 'price' => 57550]);

    $fertilizer = collect($this->getJson('/api/v1/fertilizer-prices/country/TZ?per_page=100')->json('data'))->sortBy('sort_order')->values();
    $cassava = collect($this->getJson('/api/v1/cassava-prices/country/TZ')->json('data'))->sortBy('average_price')->values();
    $investment = collect($this->getJson('/api/v1/investment-amounts/country/TZ')->json('data'))->sortBy('sort_order')->values();

    // fertiliser: "I don't know" (price 0), four prices, "Exact price" (negative)
    expect($fertilizer->pluck('label')->all())->toBe(["I don't know", '35,000 TSh', '45,000 TSh', '55,000 TSh', '65,000 TSh', 'Exact price'])
        ->and($fertilizer->first()['price_range'])->toBe("I don't know")
        ->and($fertilizer->first()['description'])->toBe("I don't know")
        ->and($fertilizer->last()['description'])->toBe('Exact price');

    // ranges: the exact-price row is words, the bands are ranges with the currency
    expect($cassava->pluck('label')->all())->toBe(['Exact price', '108,000 – 144,000 TSh', '144,000 – 180,000 TSh', '180,000 – 216,000 TSh', '216,000 – 252,000 TSh'])
        ->and($cassava->first()['description'])->toBe('Exact price')
        ->and($cassava->last()['description'])->toBe('');

    // investment: the 0 row is where the farmer types their own amount
    expect($investment->first()['label'])->toBe('Exact amount')
        ->and($investment->pluck('label')->all())->toContain('57,550 TSh', '345,300 TSh');
});

it('rounds to the currency step and does not round an unlisted currency', function () {
    viewCountry('GH');
    viewCountry('XX');
    viewFertilizer('GH', 'urea', 'UREA_GH');
    viewFertilizer('XX', 'urea', 'UREA_XX');
    viewPrice(['country' => 'GH', 'fertilizer_type' => 'UREA', 'currency' => 'GHS', 'price' => 33]);
    viewPrice(['country' => 'XX', 'fertilizer_type' => 'UREA', 'currency' => 'XYZ', 'price' => 100]);

    expect(viewOptions('UREA_GH'))->toBe([25.0, 30.0, 35.0, 45.0])
        ->and(viewOptions('UREA_XX'))->toBe([70.0, 90.0, 110.0, 130.0]);
});

it('uses the subsidised price only when the country basis is subsidised', function () {
    viewCountry('TZ', 'subsidised');
    viewCountry('RW', 'open_market');
    viewFertilizer('TZ', 'urea', 'UREA_TZ');
    viewFertilizer('RW', 'urea', 'UREA_RW');
    viewPrice(['subsidised_price' => 30000]);
    viewPrice(['country' => 'RW', 'fertilizer_type' => 'UREA', 'currency' => 'RWF', 'subsidised_price' => 30000]);

    expect(viewOptions('UREA_TZ'))->toBe([21000.0, 27000.0, 33000.0, 39000.0])
        ->and(viewOptions('UREA_RW'))->toBe([35000.0, 45000.0, 55000.0, 65000.0]);
});

it('serves only active, national, effective prices that have a fertiliser key', function () {
    viewCountry('TZ');
    foreach (['DAP', 'MOP', 'TSP', 'CAN', 'SSP'] as $label) {
        viewFertilizer('TZ', $label, $label . '_TZ');
    }
    viewPrice(['item' => 'DAP', 'fertilizer_type' => 'DAP', 'status' => 'draft']);
    viewPrice(['item' => 'MOP', 'fertilizer_type' => 'MOP', 'region' => 'Arusha']);
    viewPrice(['item' => 'TSP', 'fertilizer_type' => 'TSP', 'effective_from' => now()->addWeek()->toDateString()]);
    viewPrice(['item' => 'CAN', 'fertilizer_type' => null]);
    viewPrice(['item' => 'SSP', 'fertilizer_type' => 'SSP', 'effective_from' => now()->subDay()->toDateString()]);

    $withOptions = DB::table('fertilizer_prices')->where('price_per_bag', '>', 0)->distinct()->pluck('fertilizer_key')->all();

    expect($withOptions)->toBe(['SSP_TZ']);
});

it('gives a priced fertiliser the "I dont know" and "Exact price" rows, and an unpriced one nothing', function () {
    viewCountry('TZ');
    viewFertilizer('TZ', 'urea', 'UREA_TZ');
    viewFertilizer('TZ', 'NafakaPlus', 'NAFAKAPLUS_TZ'); // no catalogue price at all
    viewPrice();

    $markers = DB::table('fertilizer_prices')->where('price_per_bag', '<=', 0)->orderBy('fertilizer_key')->orderBy('sort_order')->get();

    // Only a fertiliser with an active price is listed; the two markers alone would leave the farmer nothing to choose
    expect($markers->pluck('fertilizer_key')->all())->toBe(['UREA_TZ', 'UREA_TZ'])
        ->and($markers->pluck('sort_order')->map(fn ($s) => (int) $s)->all())->toBe([0, 5])
        ->and((float) $markers->first()->price_per_bag)->toBe(0.0)
        ->and((float) $markers->get(1)->price_per_bag)->toBeLessThan(0.0);

    expect(viewOptions('NAFAKAPLUS_TZ'))->toBe([])
        ->and(DB::table('fertilizer_prices')->where('fertilizer_key', 'NAFAKAPLUS_TZ')->count())->toBe(0);

    // A draft price does not count: only an active one lists the fertiliser
    DB::table('default_prices')->where('item', 'urea')->update(['status' => 'draft']);
    expect(DB::table('fertilizer_prices')->where('fertilizer_key', 'UREA_TZ')->count())->toBe(0);
});

it('keeps a row id when its price changes and follows the price immediately', function () {
    viewCountry('TZ');
    viewFertilizer('TZ', 'urea', 'UREA_TZ');
    $id = viewPrice();

    $before = DB::table('fertilizer_prices')->where('fertilizer_key', 'UREA_TZ')->where('sort_order', 2)->first();
    DB::table('default_prices')->where('id', $id)->update(['price' => 60000]);
    $after = DB::table('fertilizer_prices')->where('fertilizer_key', 'UREA_TZ')->where('sort_order', 2)->first();

    expect($after->id)->toBe($before->id)
        ->and((float) $before->price_per_bag)->toBe(45000.0)
        ->and((float) $after->price_per_bag)->toBe(54000.0);
});

it('follows the steps and rounding tables', function () {
    viewCountry('TZ');
    viewFertilizer('TZ', 'urea', 'UREA_TZ');
    viewPrice(['price' => 12345]);

    expect(viewOptions('UREA_TZ'))->toBe([8600.0, 11100.0, 13600.0, 16000.0]);

    DB::table('price_choice_rounding')->where('currency', 'TZS')->update(['step' => 1000]);
    DB::table('price_choice_steps')->where('step_order', 4)->update(['change_pct' => 50]);

    expect(viewOptions('UREA_TZ'))->toBe([9000.0, 11000.0, 14000.0, 19000.0]);
});

it('is read-only: the app lists cannot be written to', function () {
    viewCountry('TZ');

    expect(fn () => FertilizerPrice::query()->create([
        'country' => 'TZ', 'fertilizer_key' => 'UREA_TZ', 'min_price' => 1, 'max_price' => 1,
        'price_per_bag' => 1, 'sort_order' => 1,
    ]))->toThrow(QueryException::class);
});

it('serves cassava as contiguous bands whose average is each step, plus the exact-price row', function () {
    viewCountry('NG');
    viewPrice(['country' => 'NG', 'item' => 'cassUP', 'fertilizer_type' => null, 'unit' => 'per_tonne', 'currency' => 'NGN', 'price' => 12000]);

    $rows = DB::table('cassava_prices')->where('country', 'NG')->orderBy('min_local_price')->get();

    expect($rows->map(fn ($r) => [(float) $r->min_local_price, (float) $r->max_local_price, (int) $r->min_price, (int) $r->max_price])->all())->toBe([
        [-1.0, -1.0, 0, 0],
        [7200.0, 9600.0, 1, 0],
        [9600.0, 12000.0, 0, 0],
        [12000.0, 14400.0, 0, 0],
        [14400.0, 16800.0, 0, 1],
    ]);
});

it('writes the band rule once: price_bands holds any non-bag item, the crop views filter it', function () {
    viewCountry('NG');
    viewPrice(['country' => 'NG', 'item' => 'cassUP', 'fertilizer_type' => null, 'unit' => 'per_tonne', 'currency' => 'NGN', 'price' => 12000]);
    viewPrice(['country' => 'NG', 'item' => 'maize_grain', 'fertilizer_type' => null, 'unit' => 'per_kg', 'currency' => 'NGN', 'price' => 230]);
    viewPrice(['country' => 'NG', 'item' => 'urea', 'fertilizer_type' => null, 'unit' => 'per_bag', 'currency' => 'NGN', 'price' => 9000]);

    $items = DB::table('price_bands')->distinct()->orderBy('item')->pluck('item')->all();

    expect($items)->toBe(['cassUP', 'maize_grain'])               // fertiliser (per bag) is not a band item
        ->and(DB::table('price_bands')->where('item', 'cassUP')->where('step_order', '>', 0)->count())->toBe(4)
        ->and(DB::table('price_bands')->where('item', 'cassUP')->where('step_order', 0)->value('min_local_price'))->toBe('-1.00')
        ->and(DB::table('cassava_prices')->where('min_local_price', '>', 0)->count())->toBe(4);
});

it('serves sweet potato bands only for countries with a sweetPotato_tuber price', function () {
    viewCountry('TZ');
    viewCountry('NG');
    viewPrice(['item' => 'sweetPotato_tuber', 'fertilizer_type' => null, 'unit' => 'per_tonne', 'price' => 120000]);

    expect(DB::table('potato_prices')->where('country', 'TZ')->where('min_local_price', '>', 0)->count())->toBe(4)
        ->and(DB::table('potato_prices')->where('country', 'NG')->count())->toBe(0);
});

it('serves maize grain per tonne and fresh cob per cob, each with its exact-price row', function () {
    viewCountry('NG');
    viewPrice(['country' => 'NG', 'item' => 'maize_grain', 'fertilizer_type' => null, 'unit' => 'per_kg', 'currency' => 'NGN', 'price' => 230]);
    viewPrice(['country' => 'NG', 'item' => 'maize_freshCob', 'fertilizer_type' => null, 'unit' => 'per_cob', 'currency' => 'NGN', 'price' => 50]);

    $grain = DB::table('maize_prices')->where('produce_type', 'grain')->where('min_local_price', '>', 0)->orderBy('min_local_price')->get();
    $cob = DB::table('maize_prices')->where('produce_type', 'cob')->where('min_local_price', '>', 0)->orderBy('min_local_price')->get();

    // 230 per kg is 230,000 per tonne, the unit of every produce list (the app converts to the
    // farmer's unit of sale as average x weight / 1000), rounded to 100 naira
    expect($grain->map(fn ($r) => [(float) $r->min_local_price, (float) $r->max_local_price])->all())->toBe([
        [138000.0, 184000.0], [184000.0, 230000.0], [230000.0, 276000.0], [276000.0, 322000.0],
    ]);

    // 50 per cob keeps its own rounding of 5, which the naira step of 100 would round to zero
    expect($cob->map(fn ($r) => [(float) $r->min_local_price, (float) $r->max_local_price])->all())->toBe([
        [30.0, 40.0], [40.0, 50.0], [50.0, 60.0], [60.0, 70.0],
    ])->and(DB::table('maize_prices')->where('min_local_price', -1)->count())->toBe(2);
});

it('serves investment amounts as multiples of the reference amount, with a 0 row', function () {
    viewCountry('TZ');
    viewCountry('NG');
    viewPrice(['item' => 'investment_amount', 'fertilizer_type' => null, 'unit' => 'per_acre', 'price' => 57550]);

    $amounts = DB::table('investment_amount')->where('country', 'TZ')->orderBy('sort_order')->pluck('investment_amount')->map(fn ($a) => (float) $a)->all();

    expect($amounts)->toBe([0.0, 57550.0, 115100.0, 172650.0, 230200.0, 345300.0])
        ->and(DB::table('investment_amount')->where('country', 'NG')->count())->toBe(0)
        ->and(DB::table('investment_amount')->where('country', 'TZ')->value('area_unit'))->toBe('acre');
});

it('serves operation costs as bands around the mapped labour price', function () {
    viewCountry('NG');
    viewPrice(['country' => 'NG', 'item' => 'manual_ploughing', 'fertilizer_type' => null, 'unit' => 'per_acre', 'currency' => 'NGN', 'price' => 17000]);
    viewPrice(['country' => 'NG', 'item' => 'weeding2', 'fertilizer_type' => null, 'unit' => 'per_acre', 'currency' => 'NGN', 'price' => 30000]);

    $rows = DB::table('operation_costs')->where('country_code', 'NG')->orderBy('min_cost')->get();

    // weeding2 is not mapped to an operation; tillage/manual follows manual_ploughing
    expect($rows->pluck('operation_name')->unique()->values()->all())->toBe(['tillage'])
        ->and($rows->pluck('operation_type')->unique()->values()->all())->toBe(['manual'])
        ->and($rows->map(fn ($r) => [(float) $r->min_cost, (float) $r->max_cost])->all())->toBe([
            [10200.0, 13600.0], [13600.0, 17000.0], [17000.0, 20400.0], [20400.0, 23800.0],
        ]);
});

it('follows the units, multiples and operation mapping tables', function () {
    viewCountry('NG');
    viewPrice(['country' => 'NG', 'item' => 'maize_grain', 'fertilizer_type' => null, 'unit' => 'per_kg', 'currency' => 'NGN', 'price' => 230]);
    viewPrice(['country' => 'NG', 'item' => 'investment_amount', 'fertilizer_type' => null, 'unit' => 'per_acre', 'currency' => 'NGN', 'price' => 10000]);

    DB::table('price_choice_units')->where('item', 'maize_grain')->update(['quantity' => 100]);
    DB::table('price_choice_multiples')->where('step_order', 5)->update(['multiple' => 10]);

    expect((float) DB::table('maize_prices')->where('produce_type', 'grain')->where('min_local_price', '>', 0)->min('min_local_price'))->toBe(13800.0)
        ->and((float) DB::table('investment_amount')->max('investment_amount'))->toBe(100000.0);
});

it('serves the options through the public endpoints', function () {
    viewCountry('TZ');
    viewFertilizer('TZ', 'urea', 'UREA_TZ');
    viewPrice();
    DB::table('currencies')->insert(['currency_code' => 'TZS', 'currency_symbol' => 'TSh', 'currency_name' => 'Tanzanian shilling', 'country_code' => 'TZ']);

    $response = $this->getJson('/api/v1/fertilizer-prices/country/TZ');

    $response->assertOk();
    expect(collect($response->json('data'))->where('price_per_bag', '>', 0)->pluck('price_per_bag')->map(fn ($p) => (float) $p)->sort()->values()->all())
        ->toBe([35000.0, 45000.0, 55000.0, 65000.0]);
});
