<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service\Playground;

class DroidRequestBuilder
{
    /** The request body RecommendationService::compute() takes, from the validated playground form */
    public function build(array $data, int $userId): array
    {
        $countryCode = strtoupper($data['country_code']);
        $lang = $data['lang'] ?? 'en';
        $cassavaProduce = $data['cassava_produce_type'] ?? 'roots';
        $maizeProduce = $data['maize_produce_type'] ?? 'fresh_cob';
        $potatoProduce = $data['sweet_potato_produce_type'] ?? 'tubers';

        return [
            'user_info' => [
                'device_token' => 'playground-' . $userId,
                'farm_name' => 'Demo Farm',
                'first_name' => 'Demo',
                'last_name' => 'User',
                'user_name' => 'demo_user',
                'email_address' => null,
                'phone_number' => null,
                'gender' => 'M',
                'send_email' => false,
                'send_sms' => false,
                'risk_attitude' => (int)$data['risk_attitude'],
            ],
            'compute_request' => [
                'farmInformation' => [
                    'country_code' => $countryCode,
                    'use_case' => strtoupper($data['use_case']),
                    'map_lat' => (float)$data['map_lat'],
                    'map_long' => (float)$data['map_long'],
                    'field_size' => (float)$data['field_size'],
                    'area_unit' => $data['area_unit'],
                ],
                'interCropping' => [
                    'inter_cropped_crop' => null,
                    'inter_cropping_maize_rec' => (bool)($data['inter_cropping_maize_rec'] ?? false),
                    'inter_cropping_potato_rec' => (bool)($data['inter_cropping_potato_rec'] ?? false),
                ],
                'recommendations' => [
                    'lang' => $lang,
                    'fertilizer_rec' => (bool)$data['fertilizer_rec'],
                    'planting_practices_rec' => (bool)$data['planting_practices_rec'],
                    'scheduled_planting_rec' => (bool)$data['scheduled_planting_rec'],
                    'scheduled_harvest_rec' => (bool)$data['scheduled_harvest_rec'],
                    'planting_date_cultivar_rec' => (bool)($data['planting_date_cultivar_rec'] ?? false),
                ],
                'planting' => [
                    'planting_date' => $data['planting_date'],
                    'planting_date_window' => (int)$data['planting_date_window'],
                    'harvest_date' => $data['harvest_date'],
                    'harvest_date_window' => (int)$data['harvest_date_window'],
                ],
                'fallow' => [
                    'fallow_type' => 'none',
                    'fallow_height' => 100,
                    'fallow_green' => false,
                ],
                'tractorCosts' => [
                    'tractor_plough' => false,
                    'tractor_harrow' => false,
                    'tractor_ridger' => false,
                    'cost_lmo_area_basis' => $data['area_unit'],
                    'cost_tractor_ploughing' => 0.0,
                    'cost_tractor_harrowing' => 0.0,
                    'cost_tractor_ridging' => 0.0,
                ],
                'manualCosts' => [
                    'cost_manual_ploughing' => 0.0,
                    'cost_manual_harrowing' => 0.0,
                    'cost_manual_ridging' => 0.0,
                ],
                'weedingCosts' => [
                    'cost_weeding_one' => 0.0,
                    'cost_weeding_two' => 0.0,
                ],
                'operationsDone' => [
                    'ploughing_done' => false,
                    'harrowing_done' => false,
                    'ridging_done' => false,
                ],
                'methods' => [
                    'method_ploughing' => 'manual',
                    'method_harrowing' => 'manual',
                    'method_ridging' => 'manual',
                    'method_weeding' => 'manual',
                ],
                'yieldInfo' => [
                    'current_field_yield' => (int)$data['field_yield'],
                    'current_maize_performance' => (float)$data['soil_quality'],
                    'sell_to_starch_factory' => false,
                    'starch_factory_name' => null,
                ],
                // The price is per selling unit (1, 50, 100 or 1000 kg, from cassava-units)
                'cassava' => [
                    'produce_type' => $cassavaProduce,
                    'unit_weight' => (float)$data['cassava_unit_weight'],
                    'unit_price' => (float)$data['cassava_unit_price'],
                ],
                // Maize is priced per cob, or per 50 kg bag of grain; sweet potato per tonne
                'maize' => [
                    'produce_type' => $maizeProduce,
                    'unit_weight' => $maizeProduce === 'grain' ? 50.0 : 1.0,
                    'unit_price' => (float)($data['maize_unit_price'] ?? 0),
                ],
                'sweetPotato' => [
                    'produce_type' => $potatoProduce,
                    'unit_weight' => 1000.0,
                    'unit_price' => (float)($data['sweet_potato_unit_price'] ?? 0),
                ],
                'maxInvestment' => 0.0,
            ],
            'fertilizer_list' => $data['fertilizer_list'],
        ];
    }
}
