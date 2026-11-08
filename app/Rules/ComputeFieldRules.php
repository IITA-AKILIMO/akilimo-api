<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Rules;

use App\Http\Enums\EnumAreaUnit;

class ComputeFieldRules
{
    public static function rules(): array
    {

        return [
            'compute_request' => ['required', 'array'],

            // Farm Information
            'compute_request.farmInformation.country_code' => ['required', 'string', 'size:2'],
            'compute_request.farmInformation.use_case' => ['required', 'string', 'max:10'],
            'compute_request.farmInformation.map_lat' => ['required', 'numeric', 'between:-90,90'],
            'compute_request.farmInformation.map_long' => ['required', 'numeric', 'between:-180,180'],
            'compute_request.farmInformation.field_size' => ['required', 'numeric', 'min:1'],
            'compute_request.farmInformation.area_unit' => [
                'required',
                'string',
                new CaseInsensitiveIn(EnumAreaUnit::values()),
            ],

            // Intercropping
            'compute_request.interCropping.inter_cropped_crop' => ['nullable', 'string', 'max:255'],
            'compute_request.interCropping.inter_cropping_maize_rec' => ['boolean'],
            'compute_request.interCropping.inter_cropping_potato_rec' => ['boolean'],

            // Recommendations
            'compute_request.recommendations.lang' => ['required', 'string', 'min:2', 'max:2'],
            'compute_request.recommendations.fertilizer_rec' => ['boolean'],
            'compute_request.recommendations.planting_practices_rec' => ['boolean'],
            'compute_request.recommendations.scheduled_planting_rec' => ['boolean'],
            'compute_request.recommendations.scheduled_harvest_rec' => ['boolean'],

            // Planting Dates
            'compute_request.planting.planting_date' => ['required', 'date'],
            'compute_request.planting.harvest_date' => ['required', 'date'],
            'compute_request.planting.planting_date_window' => ['integer', 'min:0'],
            'compute_request.planting.harvest_date_window' => ['integer', 'min:0'],

            // Fallow
            'compute_request.fallow.fallow_type' => ['required', 'string', 'max:50'],
            'compute_request.fallow.fallow_height' => ['numeric', 'min:0'],
            'compute_request.fallow.fallow_green' => ['boolean'],

            // Tractor Costs
            'compute_request.tractorCosts.tractor_plough' => ['boolean'],
            'compute_request.tractorCosts.tractor_harrow' => ['boolean'],
            'compute_request.tractorCosts.tractor_ridger' => ['boolean'],
            'compute_request.tractorCosts.cost_lmo_area_basis' => ['string', 'max:50'],
            'compute_request.tractorCosts.cost_tractor_ploughing' => ['numeric', 'min:0'],
            'compute_request.tractorCosts.cost_tractor_harrowing' => ['numeric', 'min:0'],
            'compute_request.tractorCosts.cost_tractor_ridging' => ['numeric', 'min:0'],

            // Manual Costs
            'compute_request.manualCosts.cost_manual_ploughing' => ['numeric', 'min:0'],
            'compute_request.manualCosts.cost_manual_harrowing' => ['numeric', 'min:0'],
            'compute_request.manualCosts.cost_manual_ridging' => ['numeric', 'min:0'],

            // Weeding Costs
            'compute_request.weedingCosts.cost_weeding_one' => ['numeric', 'min:0'],
            'compute_request.weedingCosts.cost_weeding_two' => ['numeric', 'min:0'],

            // Operations Done
            'compute_request.operationsDone.ploughing_done' => ['boolean'],
            'compute_request.operationsDone.harrowing_done' => ['boolean'],
            'compute_request.operationsDone.ridging_done' => ['boolean'],

            // Methods
            'compute_request.methods.method_ploughing' => ['nullable', 'string', 'max:255'],
            'compute_request.methods.method_harrowing' => ['nullable', 'string', 'max:255'],
            'compute_request.methods.method_ridging' => ['nullable', 'string', 'max:255'],
            'compute_request.methods.method_weeding' => ['nullable', 'string', 'max:255'],

            // Yield Info
            'compute_request.yieldInfo.current_field_yield' => ['numeric', 'min:0'],
            'compute_request.yieldInfo.current_maize_performance' => ['numeric', 'min:0'],
            'compute_request.yieldInfo.sell_to_starch_factory' => ['boolean'],
            'compute_request.yieldInfo.starch_factory_name' => ['nullable', 'string', 'max:255'],

            // Cassava
            'compute_request.cassava.produce_type' => ['required', 'string', 'max:50'],
            'compute_request.cassava.unit_weight' => ['numeric', 'min:0'],
            'compute_request.cassava.unit_price' => ['numeric', 'min:0'],
            'compute_request.cassava.unit_price_maize_1' => ['numeric', 'min:0'],
            'compute_request.cassava.unit_price_maize_2' => ['numeric', 'min:0'],
            'compute_request.cassava.unit_price_potato_1' => ['numeric', 'min:0'],
            'compute_request.cassava.unit_price_potato_2' => ['numeric', 'min:0'],

            // Maize
            'compute_request.maize.produce_type' => ['required', 'string', 'max:50'],
            'compute_request.maize.unit_weight' => ['numeric', 'min:0'],
            'compute_request.maize.unit_price' => ['numeric', 'min:0'],
            'compute_request.maize.unit_price_maize_1' => ['numeric', 'min:0'],
            'compute_request.maize.unit_price_maize_2' => ['numeric', 'min:0'],
            'compute_request.maize.unit_price_potato_1' => ['numeric', 'min:0'],
            'compute_request.maize.unit_price_potato_2' => ['numeric', 'min:0'],

            // Sweet Potato
            'compute_request.sweetPotato.produce_type' => ['required', 'string', 'max:50'],
            'compute_request.sweetPotato.unit_weight' => ['numeric', 'min:0'],
            'compute_request.sweetPotato.unit_price' => ['numeric', 'min:0'],
            'compute_request.sweetPotato.unit_price_maize_1' => ['numeric', 'min:0'],
            'compute_request.sweetPotato.unit_price_maize_2' => ['numeric', 'min:0'],
            'compute_request.sweetPotato.unit_price_potato_1' => ['numeric', 'min:0'],
            'compute_request.sweetPotato.unit_price_potato_2' => ['numeric', 'min:0'],

            // Investment
            'compute_request.maxInvestment' => ['numeric', 'min:0'],
        ];

    }

    public static function messages(): array
    {
        return [
            '*.required' => 'The :attribute field is required.',
            '*.numeric' => 'The :attribute must be a number.',
            '*.string' => 'The :attribute must be a string.',
            '*.boolean' => 'The :attribute must be true or false.',
            '*.in' => 'The selected value for :attribute is invalid.',
            '*.min' => 'The :attribute must be at least :min.',
            '*.size' => 'The :attribute must be exactly :size characters.',
            '*.date_format' => 'The :attribute must be in the format m/d/Y.',
            '*.between' => 'The :attribute must be between :min and :max.',
            '*.nullable' => 'The :attribute can be null.',
            'compute_request.farmInformation.area_unit.in' => 'The area unit :input must be one of: ' . implode(', ', EnumAreaUnit::values()) . '.',
        ];
    }
}
