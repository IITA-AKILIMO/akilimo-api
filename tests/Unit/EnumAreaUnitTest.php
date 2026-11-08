<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Http\Enums\EnumAreaUnit;

it('normalizes known lowercase values to their enum case', function () {
    expect(EnumAreaUnit::normalize('acre'))->toBe(EnumAreaUnit::Acre)
        ->and(EnumAreaUnit::normalize('ha'))->toBe(EnumAreaUnit::Ha)
        ->and(EnumAreaUnit::normalize('m2'))->toBe(EnumAreaUnit::M2)
        ->and(EnumAreaUnit::normalize('are'))->toBe(EnumAreaUnit::Are);
});

it('normalizes uppercase values case-insensitively', function () {
    expect(EnumAreaUnit::normalize('ACRE'))->toBe(EnumAreaUnit::Acre)
        ->and(EnumAreaUnit::normalize('HA'))->toBe(EnumAreaUnit::Ha)
        ->and(EnumAreaUnit::normalize('M2'))->toBe(EnumAreaUnit::M2)
        ->and(EnumAreaUnit::normalize('ARE'))->toBe(EnumAreaUnit::Are);
});

it('normalizes mixed-case values', function () {
    expect(EnumAreaUnit::normalize('Acre'))->toBe(EnumAreaUnit::Acre)
        ->and(EnumAreaUnit::normalize('Ha'))->toBe(EnumAreaUnit::Ha);
});

it('defaults to Acre for unknown values', function (string $unknown) {
    expect(EnumAreaUnit::normalize($unknown))->toBe(EnumAreaUnit::Acre);
})->with(['ekari', 'hekta', 'feet', 'meter', '', 'xyz']);

it('values() returns all enum string values', function () {
    $values = EnumAreaUnit::values();

    expect($values)
        ->toContain('acre')
        ->toContain('ha')
        ->toContain('m2')
        ->toContain('are')
        ->toHaveCount(4);
});

it('normalize result value matches the input after lowercasing', function () {
    expect(EnumAreaUnit::normalize('HA')->value)->toBe('ha');
    expect(EnumAreaUnit::normalize('ACRE')->value)->toBe('acre');
});
