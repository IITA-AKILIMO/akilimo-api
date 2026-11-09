<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Rules\CaseInsensitiveIn;

it('passes for an exact lowercase match', function () {
    $rule = new CaseInsensitiveIn(['acre', 'ha', 'm2']);
    expect($rule->passes('area_unit', 'acre'))->toBeTrue();
});

it('passes when the input is uppercase', function () {
    $rule = new CaseInsensitiveIn(['acre', 'ha', 'm2']);
    expect($rule->passes('area_unit', 'ACRE'))->toBeTrue();
});

it('passes when the input is mixed case', function () {
    $rule = new CaseInsensitiveIn(['acre', 'ha', 'm2']);
    expect($rule->passes('area_unit', 'Acre'))->toBeTrue();
});

it('passes when the allowed list itself was provided in uppercase', function () {
    $rule = new CaseInsensitiveIn(['ACRE', 'HA']);
    expect($rule->passes('area_unit', 'acre'))->toBeTrue()
        ->and($rule->passes('area_unit', 'HA'))->toBeTrue();
});

it('fails for a value not in the allowed list', function () {
    $rule = new CaseInsensitiveIn(['acre', 'ha']);
    expect($rule->passes('area_unit', 'miles'))->toBeFalse();
});

it('fails for an empty string when not in allowed list', function () {
    $rule = new CaseInsensitiveIn(['acre', 'ha']);
    expect($rule->passes('area_unit', ''))->toBeFalse();
});

it('error message contains the invalid input value', function () {
    $rule = new CaseInsensitiveIn(['acre', 'ha']);
    $rule->passes('area_unit', 'miles');

    expect($rule->message())->toContain('miles');
});

it('error message lists all allowed values', function () {
    $rule = new CaseInsensitiveIn(['acre', 'ha', 'm2']);
    $rule->passes('area_unit', 'feet');

    expect($rule->message())
        ->toContain('acre')
        ->toContain('ha')
        ->toContain('m2');
});
