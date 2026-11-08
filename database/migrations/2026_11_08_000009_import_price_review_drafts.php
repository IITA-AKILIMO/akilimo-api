<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Imports the labour-cost review candidates in docs/data/labour_costs_candidates.csv
 * as draft rows, so country teams start from the November 2026 desk research
 * instead of an empty list (docs/PRICE_CATALOGUE_DESIGN.md, phase 1).
 *
 * They stay out of GET /v1/prices and GET /v1/default-prices until a reviewer
 * activates them. Existing rows are never overwritten: the import only adds
 * the (country, item) pairs that have no row yet.
 *
 * Starch-factory drafts are not imported here: they live in `starch_prices`,
 * which already has its own per-factory active flag.
 */
return new class extends Migration {
    /**
     * country, item, price, currency, confidence, source basis
     *
     * @var array<int, array{0: string, 1: string, 2: float, 3: string, 4: string, 5: string}>
     */
    private const ROWS = [
        ['RW', 'manual_ploughing', 20000, 'RWF', 'low', '2,000 RWF/day x 10 person-days'],
        ['RW', 'manual_ridging', 16000, 'RWF', 'low', '2,000 RWF/day x 8 person-days'],
        ['RW', 'weeding1', 16000, 'RWF', 'low', '2,000 RWF/day x 8 person-days'],
        ['RW', 'weeding2', 12000, 'RWF', 'low', '2,000 RWF/day x 6 person-days'],

        ['BI', 'manual_ploughing', 43000, 'BIF', 'low', '4,300 BIF/day x 10 person-days'],
        ['BI', 'manual_ridging', 34000, 'BIF', 'low', '4,300 BIF/day x 8 person-days'],
        ['BI', 'weeding1', 34000, 'BIF', 'low', '4,300 BIF/day x 8 person-days'],
        ['BI', 'weeding2', 26000, 'BIF', 'low', '4,300 BIF/day x 6 person-days'],

        ['GH', 'manual_ploughing', 550, 'GHS', 'low', '55 GHS/day x 10 person-days'],
        ['GH', 'manual_ridging', 440, 'GHS', 'low', '55 GHS/day x 8 person-days'],
        ['GH', 'weeding1', 440, 'GHS', 'low', '55 GHS/day x 8 person-days'],
        ['GH', 'weeding2', 330, 'GHS', 'low', '55 GHS/day x 6 person-days'],
        ['GH', 'tractor_ploughing', 250, 'GHS', 'medium', 'Reported tractor ploughing rate, 2023-2024'],
        ['GH', 'tractor_ridging', 250, 'GHS', 'low', 'Assumed equal to ploughing; one per-acre rate per operation'],
    ];

    public function up(): void
    {
        $now = now();
        $rows = [];

        foreach (self::ROWS as [$country, $item, $price, $currency, $confidence, $basis]) {
            $rows[] = [
                'country' => $country,
                'region' => null,
                'item' => $item,
                'price' => $price,
                'subsidised_price' => null,
                'unit' => 'per_acre',
                'bag_kg' => null,
                'currency' => $currency,
                'min_price' => null,
                'max_price' => null,
                'status' => 'draft',
                'source' => substr('Desk research 2026-11-08: ' . $basis, 0, 255),
                'source_date' => '2026-11-08',
                'confidence' => $confidence,
                'effective_from' => null,
                'updated_by' => null,
                'submitted_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('default_prices')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        $pairs = array_map(fn (array $row) => [$row[0], $row[1]], self::ROWS);

        foreach ($pairs as [$country, $item]) {
            DB::table('default_prices')
                ->where('country', $country)
                ->where('item', $item)
                ->where('status', 'draft')
                ->where('source', 'like', 'Desk research 2026-11-08:%')
                ->delete();
        }
    }
};
