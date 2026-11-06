<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

use App\Models\Crop;

function addCrop(string $key, array $attributes = []): Crop
{
    return Crop::create(array_merge([
        'crop_key' => $key,
        'name' => ucfirst($key),
        'active' => true,
        'advice_available' => false,
        'countries' => null,
        'sort_order' => 10,
    ], $attributes));
}

it('lists cassava after migrating, with recommendations available', function () {
    $this->getJson('/api/v1/crops')
        ->assertOk()
        ->assertJsonFragment(['key' => 'cassava', 'advice_available' => true]);
});

it('lists active crops in display order and hides switched-off ones', function () {
    addCrop('maize', ['sort_order' => 3]);
    addCrop('yam', ['sort_order' => 2, 'active' => false]);

    $keys = collect($this->getJson('/api/v1/crops')->assertOk()->json('data'))->pluck('key')->all();

    expect($keys)->toBe(['cassava', 'maize']);
});

it('offers a crop only in the countries it names, and a crop with no list everywhere', function () {
    addCrop('sweet_potato', ['countries' => ['TZ']]);

    $tz = collect($this->getJson('/api/v1/crops/country/TZ')->assertOk()->json('data'))->pluck('key')->all();
    $ng = collect($this->getJson('/api/v1/crops/country/ng')->assertOk()->json('data'))->pluck('key')->all();

    expect($tz)->toContain('cassava', 'sweet_potato')
        ->and($ng)->toContain('cassava')
        ->and($ng)->not->toContain('sweet_potato');
});

it('answers with the crop fields clients need', function () {
    addCrop('maize', ['countries' => ['NG'], 'description' => 'Grain and fresh cobs']);

    $maize = collect($this->getJson('/api/v1/crops')->json('data'))->firstWhere('key', 'maize');

    expect($maize)->toMatchArray([
        'name' => 'Maize',
        'description' => 'Grain and fresh cobs',
        'advice_available' => false,
        'countries' => ['NG'],
    ]);
});
