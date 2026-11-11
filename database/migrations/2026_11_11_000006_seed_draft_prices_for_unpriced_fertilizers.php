<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Draft prices for sold fertilizers that had no default price at all, so they
 * were left out of the fertilizer list. Found by a web search on 2026-11-11.
 *
 * They are seeded as DRAFTS with low confidence and the source written on the
 * row; nothing is served until a reviewer checks and activates each one in Default
 * prices (the review rules apply as usual). A row that already exists is left
 * alone.
 *
 * Not found online, so not seeded (someone with the local figure should add them):
 *  - NG NPK 10:10:10, RW CAN, RW SSP, TZ SSP.
 *
 * Caveats for the reviewer:
 *  - NG NPK 20:12:26 has no listing; the price is a PROXY: Indorama's published
 *    ex-Kaduna price for NPK 20-10-10 (N44,400 per 50 kg bag; retailers list about
 *    N43,000 and other price tables N46,600 to N48,500). A 20:12:26 grade usually
 *    costs a little more. The other NG prices in the catalogue are far lower
 *    (N7,200 to N8,500), so they look stale next to this one.
 *  - BI dolomite is the 2023 SUBSIDISED sale: two 25 kg bags cost 19,000 FBu in
 *    total, of which the farmer paid 5,000 FBu and the government 14,000 FBu
 *    (Burundi news agency ABP, 15 September 2023, 2024A season). It is a 2023
 *    figure, so check it against the current subsidy.
 */
return new class extends Migration {
    private const ROWS = [
        [
            'country' => 'NG',
            'item' => 'NPK201226',
            'fertilizer_type' => 'NPK_20_12_26',
            'price' => 44400,
            'subsidised_price' => null,
            'currency' => 'NGN',
            'unit' => 'per_bag',
            'bag_kg' => 50,
            'source' => 'PROXY, no 20:12:26 listing found: Indorama ex-Kaduna price for NPK 20-10-10, 50 kg (web search 2026-11-11). Check with a supplier.',
            'source_date' => null,
            'confidence' => 'low',
        ],
        [
            'country' => 'BI',
            'item' => 'DOLOMITE',
            'fertilizer_type' => 'DOLOMITE',
            'price' => 19000,
            'subsidised_price' => 5000,
            'currency' => 'BIF',
            'unit' => 'per_bag',
            'bag_kg' => 50,
            'source' => 'ABP Burundi, 2024A season preparations: two 25 kg bags 19,000 FBu, farmer pays 5,000 FBu (2023 subsidised sale). Check the current subsidy.',
            'source_date' => '2023-09-15',
            'confidence' => 'low',
        ],
    ];

    public function up(): void
    {
        foreach (self::ROWS as $row) {
            if (DB::table('default_prices')->where('country', $row['country'])->where('item', $row['item'])->exists()) {
                continue;
            }

            $id = DB::table('default_prices')->insertGetId($row + [
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('default_price_history')->insert([
                'default_price_id' => $id,
                'country' => $row['country'],
                'item' => $row['item'],
                'action' => 'created',
                'new_values' => json_encode(['price' => $row['price'], 'status' => 'draft', 'source' => $row['source']]),
                'note' => 'Seeded draft from a web search; needs review before activation.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Only a row still in draft with the seeded source goes; a price someone has reviewed or activated stays.
        // The history is never deleted: the rollback is recorded as one more entry, and the row's earlier
        // entries stay (their link to the price is cleared by the foreign key).
        foreach (self::ROWS as $row) {
            $deleted = DB::table('default_prices')
                ->where('country', $row['country'])
                ->where('item', $row['item'])
                ->where('status', 'draft')
                ->where('source', $row['source'])
                ->delete();

            if ($deleted > 0) {
                DB::table('default_price_history')->insert([
                    'default_price_id' => null,
                    'country' => $row['country'],
                    'item' => $row['item'],
                    'action' => 'rolled_back',
                    'old_values' => json_encode(['price' => $row['price'], 'status' => 'draft', 'source' => $row['source']]),
                    'new_values' => null,
                    'note' => 'Seeded draft removed by rolling back the migration that added it.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};