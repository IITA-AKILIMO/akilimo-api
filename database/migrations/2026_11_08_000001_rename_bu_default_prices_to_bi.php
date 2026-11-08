<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The legacy default-prices CSV filed Burundi under "BU" instead of its ISO code "BI", so
 * GET /v1/default-prices?country=BI returned nothing. This moves those rows to "BI" with their prices
 * unchanged. Where a "BI" row for the same item already exists it is kept and the "BU" duplicate dropped,
 * since (country, item) is unique. Safe to run more than once.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function () {
            $existing = DB::table('default_prices')->where('country', 'BI')->pluck('item')->all();

            DB::table('default_prices')->where('country', 'BU')->whereIn('item', $existing)->delete();
            DB::table('default_prices')->where('country', 'BU')->update(['country' => 'BI', 'updated_at' => now()]);
        });
    }

    public function down(): void
    {
        // Not reversible: "BU" was never a valid country code, and moved rows cannot be told apart from real "BI" rows
    }
};
