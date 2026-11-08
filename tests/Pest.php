<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function validComputePayload(): array
{
    return [
        'user_info' => [
            'device_token' => fake()->uuid(),
            'risk_attitude' => 2,
            'user_name' => 'test_user',
            'first_name' => 'Test',
            'last_name' => 'User',
            'gender' => 'M',
            'farm_name' => 'Test Farm',
            'email_address' => 'test@example.com',
            'send_sms' => false,
            'send_email' => false,
        ],
        'compute_request' => [
            'farmInformation' => [
                'country_code' => 'NG',
                'use_case' => 'CASSAVA',
                'map_lat' => 7.4,
                'map_long' => 5.2,
                'field_size' => 1.0,
                'area_unit' => 'ha',
            ],
            'interCropping' => [
                'inter_cropped_crop' => null,
                'inter_cropping_maize_rec' => false,
                'inter_cropping_potato_rec' => false,
            ],
            'recommendations' => [
                'lang' => 'en',
                'fertilizer_rec' => true,
                'planting_practices_rec' => false,
                'scheduled_planting_rec' => false,
                'scheduled_harvest_rec' => false,
            ],
            'planting' => [
                'planting_date' => '2025-04-01',
                'harvest_date' => '2025-10-01',
                'planting_date_window' => 0,
                'harvest_date_window' => 0,
            ],
            'fallow' => [
                'fallow_type' => 'NONE',
                'fallow_height' => 0,
                'fallow_green' => false,
            ],
            'tractorCosts' => [
                'tractor_plough' => false,
                'tractor_harrow' => false,
                'tractor_ridger' => false,
                'cost_lmo_area_basis' => 'ha',
                'cost_tractor_ploughing' => 0,
                'cost_tractor_harrowing' => 0,
                'cost_tractor_ridging' => 0,
            ],
            'manualCosts' => [
                'cost_manual_ploughing' => 0,
                'cost_manual_harrowing' => 0,
                'cost_manual_ridging' => 0,
            ],
            'weedingCosts' => [
                'cost_weeding_one' => 0,
                'cost_weeding_two' => 0,
            ],
            'operationsDone' => [
                'ploughing_done' => false,
                'harrowing_done' => false,
                'ridging_done' => false,
            ],
            'methods' => [
                'method_ploughing' => null,
                'method_harrowing' => null,
                'method_ridging' => null,
                'method_weeding' => null,
            ],
            'yieldInfo' => [
                'current_field_yield' => 10,
                'current_maize_performance' => 5,
                'sell_to_starch_factory' => false,
                'starch_factory_name' => '',
            ],
            'cassava' => [
                'produce_type' => 'FRESH_TUBER',
                'unit_weight' => 100,
                'unit_price' => 5000,
                'unit_price_maize_1' => 0,
                'unit_price_maize_2' => 0,
                'unit_price_potato_1' => 0,
                'unit_price_potato_2' => 0,
            ],
            'maize' => [
                'produce_type' => 'DRY_GRAIN',
                'unit_weight' => 100,
                'unit_price' => 10000,
                'unit_price_maize_1' => 0,
                'unit_price_maize_2' => 0,
                'unit_price_potato_1' => 0,
                'unit_price_potato_2' => 0,
            ],
            'sweetPotato' => [
                'produce_type' => 'FRESH_TUBER',
                'unit_weight' => 100,
                'unit_price' => 3000,
                'unit_price_maize_1' => 0,
                'unit_price_maize_2' => 0,
                'unit_price_potato_1' => 0,
                'unit_price_potato_2' => 0,
            ],
            'maxInvestment' => 50000,
        ],
        'fertilizer_list' => [],
    ];
}

function plumberSuccessResponse(): array
{
    return [
        'status' => 'success',
        'version' => '20251228',
        'data' => [
            'rec_type' => 'FR',
            'recommendation' => 'We recommend applying 50 kg of Urea per hectare',
            'data' => [],
            'fertilizer_rates' => [['type' => 'Urea', 'rate' => 50]],
        ],
    ];
}
