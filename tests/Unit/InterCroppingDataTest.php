<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Data\InterCroppingData;

it('isRecommended returns true when only maize rec is enabled', function () {
    $data = InterCroppingData::from([
        'inter_cropped_crop' => null,
        'inter_cropping_maize_rec' => true,
        'inter_cropping_potato_rec' => false,
    ]);

    expect($data->isRecommended())->toBeTrue();
});

it('isRecommended returns true when only potato rec is enabled', function () {
    $data = InterCroppingData::from([
        'inter_cropped_crop' => null,
        'inter_cropping_maize_rec' => false,
        'inter_cropping_potato_rec' => true,
    ]);

    expect($data->isRecommended())->toBeTrue();
});

it('isRecommended returns true when both recs are enabled', function () {
    $data = InterCroppingData::from([
        'inter_cropped_crop' => 'cassava',
        'inter_cropping_maize_rec' => true,
        'inter_cropping_potato_rec' => true,
    ]);

    expect($data->isRecommended())->toBeTrue();
});

it('isRecommended returns false when both recs are disabled', function () {
    $data = InterCroppingData::from([
        'inter_cropped_crop' => null,
        'inter_cropping_maize_rec' => false,
        'inter_cropping_potato_rec' => false,
    ]);

    expect($data->isRecommended())->toBeFalse();
});

it('accepts null for inter_cropped_crop', function () {
    $data = InterCroppingData::from([
        'inter_cropped_crop' => null,
        'inter_cropping_maize_rec' => false,
        'inter_cropping_potato_rec' => false,
    ]);

    expect($data->interCroppedCrop)->toBeNull();
});

it('stores a non-null inter_cropped_crop string', function () {
    $data = InterCroppingData::from([
        'inter_cropped_crop' => 'cassava',
        'inter_cropping_maize_rec' => false,
        'inter_cropping_potato_rec' => false,
    ]);

    expect($data->interCroppedCrop)->toBe('cassava');
});
