<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rwanda's fertiliser default prices (per 50 kg bag) were far below the 2026/27 season's: urea 28,200 against
 * 99,000 at retail. They become the retail (full) price, with the subsidised farmer price beside it
 * (subsidised_price), from the Ministry's 2026/27 per-kilogram prices (urea 1,980 less 890 subsidy, DAP 1,950 less
 * 890, NPK 17:17:17 1,861 less 807), x 50 kg. Which of the two the app offers is the country's price basis.
 * Only rows still holding the old value change, so a price an admin has since edited is kept. MOP is not in
 * the subsidy list and is left alone. Sources: docs/data/price-review-2026-11.md.
 */
return new class extends Migration {
    private const SOURCE = 'MINAGRI 2026/27 fertiliser prices (The New Times, AllAfrica 3 Jul 2026): retail per kg x 50; subsidised = farmer price after the subsidy';

    /** item => [old price, new price, new subsidised price] */
    private const PRICES = [
        'urea' => [28200, 99000, 54500],
        'DAP' => [31650, 97500, 53000],
        'NPK171717' => [35650, 93050, 52700],
    ];

    public function up(): void
    {
        DB::transaction(function () {
            foreach (self::PRICES as $item => [$old, $new, $subsidised]) {
                DB::table('price_catalogue')
                    ->where('country', 'RW')
                    ->where('item', $item)
                    ->where('price', $old)
                    ->update([
                        'price' => $new,
                        'subsidised_price' => $subsidised,
                        'source' => self::SOURCE,
                        'source_date' => '2026-07-03',
                        'confidence' => 'medium',
                        'updated_at' => now(),
                    ]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            foreach (self::PRICES as $item => [$old, $new, $subsidised]) {
                DB::table('price_catalogue')
                    ->where('country', 'RW')
                    ->where('item', $item)
                    ->where('price', $new)
                    ->where('subsidised_price', $subsidised)
                    ->update([
                        'price' => $old,
                        'subsidised_price' => null,
                        'source' => null,
                        'source_date' => null,
                        'confidence' => null,
                        'updated_at' => now(),
                    ]);
            }
        });
    }
};
