<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

return [
    // Applied when a country has not chosen a basis yet (decision D1).
    'default_basis' => 'open_market',

    'basis' => ['open_market', 'subsidised'],

    'statuses' => ['draft', 'pending_second_review', 'active'],

    // A change larger than this needs a second admin to confirm (decision D4).
    'second_review_threshold' => 0.30,

    'confidence_levels' => ['high', 'medium', 'low'],

    // Fertilisers are sold in 50 kg bags unless the country says otherwise
    // (Burundi FOMI is a real 25 kg bag, set per row).
    'default_bag_kg' => 50,

    // Real units per item. Items not listed here are fertiliser, per bag.
    'unit_by_item' => [
        'manual_ploughing' => 'per_acre',
        'manual_harrowing' => 'per_acre',
        'manual_ridging' => 'per_acre',
        'tractor_ploughing' => 'per_acre',
        'tractor_harrowing' => 'per_acre',
        'tractor_ridging' => 'per_acre',
        'weeding1' => 'per_acre',
        'weeding2' => 'per_acre',
        'investment_amount' => 'per_acre',
        'cassUP' => 'per_tonne',
        'sweetPotato_tuber' => 'per_tonne',
        'sweetPotato_flour' => 'per_tonne',
        'maize_grain' => 'per_kg',
        'maize_freshCob' => 'per_cob',
    ],

    // Phase 3: the price choices the mobile app shows (decision D3). The database
    // computes them live from the catalogue (the fertilizer_prices, cassava_prices
    // and potato_prices views); these values only seed its price_choice_steps and
    // price_choice_rounding tables. Change the tables afterwards, not this file.
    'choice_steps' => [-0.30, -0.10, 0.10, 0.30],

    'rounding' => [
        'NGN' => 100,
        'TZS' => 100,
        'RWF' => 100,
        'BIF' => 100,
        'GHS' => 5,
    ],
];
