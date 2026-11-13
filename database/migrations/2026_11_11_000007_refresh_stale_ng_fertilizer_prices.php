<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Nigeria's default fertilizer prices were 7,200 to 13,500 NGN per 50 kg bag, five
 * or six times below today's market (about 35,000 to 55,000). The ones below have
 * a published figure, so they are moved to it as DRAFTS with the source written on
 * the row. A draft is not served, so each stays out of the app and the fertilizer
 * list until a reviewer checks and activates it in Default prices; because the
 * change is over 30 %, a second admin must confirm it (decision D4).
 *
 * Sources (web search, 2026-11-11):
 *  - urea 50,000 and NPK 20-10-10 48,000: IFDC "Global Fertilizer Markets Today",
 *    2 June 2026 (Nigerian wholesale per 50 kg: urea 35,000 in February to 50,000
 *    in May; NPK 20-10-10 43,000 to 48,000).
 *  - NPK 15-15-15 46,900: Indorama's ex-Kaduna price list (undated; 938,000 per tonne).
 *  - CAN 39,500: the middle of the 37,000 to 42,000 average in Farmsquare's 2025
 *    guide. A 2025 figure, so it is the weakest of the four.
 *
 *  - DAP 54,250 and MOP 28,000: the Federal Government's early-procurement prices for
 *    2026 (PFI NPK; Vanguard 14 Jun 2026, NAN 14 Jun 2026): DAP USD 775 per tonne,
 *    MOP USD 400 per tonne. They are RAW-MATERIAL procurement costs in dollars, not farm-gate
 *    prices, and the articles give no exchange rate; NGN 1,400 per dollar is derived from their
 *    savings figure (USD 43.99 million = NGN 61.58 billion), so per 50 kg bag:
 *    775 x 1,400 / 20 = 54,250 and 400 x 1,400 / 20 = 28,000. Blending, transport and dealer
 *    margin are not included, and a retail listing of about 29,000 for DAP also exists, so
 *    the reviewer should treat both as low confidence.
 *
 * Left alone because nothing reliable was found (still stale, so someone with the
 * local figure should review them): TSP 13,250, NPK 17-17-17 7,800 and
 * NPK 20-12-16 8,000. SSP (22,364) is within the 20,000 to 25,000 now listed, so it stays.
 *
 * History is kept: every change adds a default_price_history entry, and a rollback
 * restores the old values and adds a rolled_back entry instead of deleting any.
 */
return new class extends Migration {
    private const NOTE = 'Stale NG price replaced by a published figure; needs review before activation.';

    /** item => [old price, new price, source, source date, confidence] */
    private const ROWS = [
        'urea' => [7500, 50000, 'IFDC Global Fertilizer Markets Today, 2 Jun 2026: Nigeria wholesale urea per 50 kg, May 2026', '2026-06-02', 'medium'],
        'NPK201010' => [7200, 48000, 'IFDC Global Fertilizer Markets Today, 2 Jun 2026: Nigeria wholesale NPK 20-10-10 per 50 kg', '2026-06-02', 'medium'],
        'NPK151515' => [8500, 46900, 'Indorama ex-Kaduna price list, NPK 15-15-15 per 50 kg (undated page, read 2026-11-11)', null, 'low'],
        'DAP' => [13250, 54250, 'Nigeria FG early procurement 2026 (Vanguard/NAN, 14 Jun 2026): DAP USD 775/t raw-material cost x about NGN 1,400/USD / 20 bags. Not a farm-gate price.', '2026-06-14', 'low'],
        'MOP' => [13500, 28000, 'Nigeria FG early procurement 2026 (Vanguard/NAN, 14 Jun 2026): MOP USD 400/t raw-material cost x about NGN 1,400/USD / 20 bags. Not a farm-gate price.', '2026-06-14', 'low'],
        'CAN' => [7500, 39500, 'Farmsquare 2025 guide: CAN average 37,000 to 42,000 per 50 kg (2025, not 2026)', null, 'low'],
    ];

    public function up(): void
    {
        foreach (self::ROWS as $item => [$old, $new, $source, $date, $confidence]) {
            $row = DB::table('default_prices')->where('country', 'NG')->where('item', $item)->first();

            // Only a row still at the stale value; a price someone already changed is theirs
            if ($row === null || (float) $row->price !== (float) $old) {
                continue;
            }

            DB::table('default_prices')->where('id', $row->id)->update([
                'price' => $new,
                'status' => 'draft',
                'submitted_by' => null,
                'source' => $source,
                'source_date' => $date,
                'confidence' => $confidence,
                'updated_at' => now(),
            ]);

            DB::table('default_price_history')->insert([
                'default_price_id' => $row->id,
                'country' => 'NG',
                'item' => $item,
                'action' => 'updated',
                'old_values' => json_encode(['price' => $old, 'status' => $row->status, 'source' => $row->source]),
                'new_values' => json_encode(['price' => $new, 'status' => 'draft', 'source' => $source]),
                'note' => self::NOTE,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach (self::ROWS as $item => [$old, $new, $source]) {
            $row = DB::table('default_prices')->where('country', 'NG')->where('item', $item)->first();

            // Restore the old value only on a row still as this migration left it
            if ($row === null || (float) $row->price !== (float) $new || $row->status !== 'draft' || $row->source !== $source) {
                continue;
            }

            DB::table('default_prices')->where('id', $row->id)->update([
                'price' => $old, 'status' => 'active', 'source' => null, 'source_date' => null,
                'confidence' => null, 'updated_at' => now(),
            ]);

            // The earlier history stays; the rollback is one more entry
            DB::table('default_price_history')->insert([
                'default_price_id' => $row->id,
                'country' => 'NG',
                'item' => $item,
                'action' => 'rolled_back',
                'old_values' => json_encode(['price' => $new, 'status' => 'draft', 'source' => $source]),
                'new_values' => json_encode(['price' => $old, 'status' => 'active', 'source' => null]),
                'note' => 'Restored by rolling back the migration that replaced this stale price.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};