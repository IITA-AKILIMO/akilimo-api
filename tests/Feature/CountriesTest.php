<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

use App\Models\Country;

it('lists only active countries in display order, with the position a map needs', function () {
    Country::query()->delete();
    Country::create(['code' => 'ZB', 'name' => 'Bravo', 'active' => true, 'sort_order' => 2, 'latitude' => 1.5, 'longitude' => 2.5,
        'min_latitude' => 1.0, 'max_latitude' => 2.0, 'min_longitude' => 2.0, 'max_longitude' => 3.0]);
    Country::create(['code' => 'ZA', 'name' => 'Alpha', 'active' => true, 'sort_order' => 1]);
    Country::create(['code' => 'ZC', 'name' => 'Charlie', 'active' => false, 'sort_order' => 3]);

    $rows = $this->getJson('/api/v1/countries')->assertOk()->json('data');

    expect(collect($rows)->pluck('code')->all())->toBe(['ZA', 'ZB'])
        ->and($rows[1])->toMatchArray(['latitude' => 1.5, 'longitude' => 2.5, 'min_latitude' => 1.0, 'max_longitude' => 3.0])
        ->and($rows[0]['latitude'])->toBeNull();
});
