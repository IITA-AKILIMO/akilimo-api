<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The maize grain price list is in kilograms x 1,000: per tonne, like cassava.
 *
 * The catalogue holds maize grain per kg (NG 230). Migration 000013 showed it per
 * 50 kg, which was an assumption. The app converts a list price to the farmer's
 * unit of sale as average x unit weight / 1000 (MathHelper.computeUnitPrice), so a
 * list is per tonne, and the farmer then chooses 1 kg, 50 kg, 100 kg (the standard
 * Nigerian bag, see maize_units) or a tonne. The old hand-made maize bands were on
 * the same scale as the cassava bands, which are per tonne.
 *
 * Fresh cob is unchanged: it is per cob.
 *
 * down() restores the 50 kg assumption.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::table('price_choice_units')->where('item', 'maize_grain')->update(['quantity' => 1000]);
    }

    public function down(): void
    {
        DB::table('price_choice_units')->where('item', 'maize_grain')->update(['quantity' => 50]);
    }
};
