<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Stale fertiliser default prices in Nigeria, Ghana and Burundi (docs/data/price-review-2026-11.md).
 * Only rows still holding the old value change, so a price an admin has since edited is kept.
 *
 * - NG: NPK 17-17-17 and 20-12-16 held 7,800 and 8,000 against 44,000-50,000 for NPK today. No listing for
 *   these grades exists, so they take Indorama's ex-Kaduna price for the nearest grade (low confidence).
 * - GH: the NPK grades held 146-160; the only retailer listing found is about 420 per 50 kg (low confidence).
 * - BI: the full prices already match the 2023 cost; the farmer's subsidised price per 25 kg bag is added.
 */
return new class extends Migration {
    private const SOURCE_NG = 'Proxy, no listing for this grade (2026-11-27): Indorama ex-Kaduna price of the nearest NPK grade per 50 kg. Check with a supplier.';
    private const SOURCE_GH = 'Undated retailer listing of NPK 20-10-10 (Ghana Chemicals), within the 400-550 open-market range of GNA 2023; grade proxy. No dated 2026 cedi price found. Check with MoFA.';
    private const SOURCE_BI = 'ABP Burundi 2023: farmer pays this per 25 kg bag under the subsidy; the official figure in 2025-26 is about 33,000 (FORSC). Confirm the 2026 price.';

    /** [country, item, old price, new price, new subsidised price, source, source date, confidence] */
    private const ROWS = [
        ['NG', 'NPK171717', 7800, 50300, null, self::SOURCE_NG, '2026-11-27', 'low'],
        ['NG', 'NPK201216', 8000, 44400, null, self::SOURCE_NG, '2026-11-27', 'low'],
        ['GH', 'NPK112221', 160, 420, null, self::SOURCE_GH, '2026-11-27', 'low'],
        ['GH', 'NPK251010', 150, 420, null, self::SOURCE_GH, '2026-11-27', 'low'],
        ['GH', 'NPK152020', 156, 420, null, self::SOURCE_GH, '2026-11-27', 'low'],
        ['GH', 'NPK201010', 146, 420, null, self::SOURCE_GH, '2026-11-27', 'low'],
        ['GH', 'NPK23105', 146, 420, null, self::SOURCE_GH, '2026-11-27', 'low'],
        ['GH', 'NPK123017', 160, 420, null, self::SOURCE_GH, '2026-11-27', 'low'],
        ['BI', 'FOMIIMBURA', 85390, 85390, 31000, self::SOURCE_BI, '2023-09-15', 'medium'],
        ['BI', 'FOMIBAGARA', 77600, 77600, 28000, self::SOURCE_BI, '2023-09-15', 'medium'],
        ['BI', 'FOMITOTAHAZA', 77600, 77600, 33000, self::SOURCE_BI, '2023-09-15', 'low'],
    ];

    public function up(): void
    {
        DB::transaction(function () {
            foreach (self::ROWS as [$country, $item, $old, $new, $subsidised, $source, $date, $confidence]) {
                DB::table('price_catalogue')
                    ->where('country', $country)
                    ->where('item', $item)
                    ->where('price', $old)
                    ->whereNull('subsidised_price')
                    ->update([
                        'price' => $new,
                        'subsidised_price' => $subsidised,
                        'source' => $source,
                        'source_date' => $date,
                        'confidence' => $confidence,
                        'updated_at' => now(),
                    ]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            foreach (self::ROWS as [$country, $item, $old, $new, $subsidised, $source]) {
                DB::table('price_catalogue')
                    ->where('country', $country)
                    ->where('item', $item)
                    ->where('price', $new)
                    ->where('source', $source)
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
