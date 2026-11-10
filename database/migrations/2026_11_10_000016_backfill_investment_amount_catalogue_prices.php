<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Give each country's investment list a reference amount in the catalogue, so
 * the list can be computed instead of typed. The reference is the country's
 * smallest existing amount per acre (TZ 57,550, GH 202.35, NG 10,271, BI 103,330),
 * which the old lists used as their first choice. Rows are added as active so
 * the lists do not change on the day of the migration; a country team reviews
 * them in the admin like any other price. A country that already has the item
 * is left alone.
 */
return new class extends Migration {
    public function up(): void
    {
        $amounts = DB::table('investment_amount')
            ->where('investment_amount', '>', 0)
            ->where('area_unit', 'acre')
            ->groupBy('country')
            ->select('country', DB::raw('MIN(investment_amount) AS reference'))
            ->get();

        foreach ($amounts as $row) {
            $exists = DB::table('default_prices')->where('country', $row->country)->where('item', 'investment_amount')->exists();

            if ($exists) {
                continue;
            }

            DB::table('default_prices')->insert([
                'country' => $row->country,
                'item' => 'investment_amount',
                'price' => $row->reference,
                'unit' => 'per_acre',
                'currency' => DB::table('currencies')->where('country_code', $row->country)->value('currency_code'),
                'status' => 'active',
                'source' => 'Backfilled from the investment_amount list: its smallest amount per acre',
                'source_date' => now()->toDateString(),
                'confidence' => 'low',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('default_prices')
            ->where('item', 'investment_amount')
            ->where('source', 'like', 'Backfilled from the investment_amount list%')
            ->delete();
    }
};
