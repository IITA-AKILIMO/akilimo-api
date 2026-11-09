<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tanzania's fertiliser default prices (per 50 kg bag) dated from about 2020. They are updated to the TFRA
 * indicative national averages of September 2025 (unsubsidised wholesale), rounded to the nearest 100 TZS.
 * Only rows still holding the old value change, so a price an admin has since edited is kept.
 * Sources: docs/data/price-review-2026-11.md.
 */
return new class extends Migration {
    /** item => [old, new] */
    private const PRICES = [
        'urea' => [58000, 77100],
        'CAN' => [53000, 61000],
        'DAP' => [60000, 125500],
        'MOP' => [67000, 76000],
        'NPK171717' => [63000, 89200],
        'NPK201010' => [68000, 89200],
        'TSP' => [64000, 103100],
    ];

    public function up(): void
    {
        $this->apply(0, 1);
    }

    public function down(): void
    {
        $this->apply(1, 0);
    }

    /**
     * @throws Throwable
     */
    private function apply(int $from, int $to): void
    {
        DB::transaction(function () use ($from, $to) {
            foreach (self::PRICES as $item => $prices) {
                DB::table('default_prices')
                    ->where('country', 'TZ')
                    ->where('item', $item)
                    ->where('price', $prices[$from])
                    ->update(['price' => $prices[$to], 'updated_at' => now()]);
            }
        });
    }
};
