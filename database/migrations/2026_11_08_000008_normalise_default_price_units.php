<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrects the units and bag sizes the old table carried.
 *
 * Every row was stored with the column default `per_bag`, so labour was priced
 * "per bag" and Burundi FOMI looked like a 50 kg bag. The real units are:
 * labour per acre, roots/tubers per tonne, maize grain per kg, cobs per cob,
 * fertiliser per bag (50 kg, except Burundi FOMI's real 25 kg bag).
 */
return new class extends Migration {
    /** item => unit */
    private const UNIT_BY_ITEM = [
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
    ];

    /** Burundi FOMI is sold in real 25 kg bags, not 50 kg. */
    private const BURUNDI_25KG = ['FOMIIMBURA', 'FOMIBAGARA', 'FOMITOTAHAZA'];

    public function up(): void
    {
        foreach (self::UNIT_BY_ITEM as $item => $unit) {
            DB::table('default_prices')
                ->where('item', $item)
                ->update(['unit' => $unit, 'bag_kg' => null, 'updated_at' => now()]);
        }

        // Everything left is fertiliser: one 50 kg bag unless Burundi says otherwise.
        DB::table('default_prices')
            ->where('unit', 'per_bag')
            ->update(['bag_kg' => 50, 'updated_at' => now()]);

        DB::table('default_prices')
            ->where('country', 'BI')
            ->whereIn('item', self::BURUNDI_25KG)
            ->update(['bag_kg' => 25, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('default_prices')
            ->whereIn('item', array_keys(self::UNIT_BY_ITEM))
            ->update(['unit' => 'per_bag', 'bag_kg' => null, 'updated_at' => now()]);

        DB::table('default_prices')->update(['bag_kg' => null, 'updated_at' => now()]);
    }
};
