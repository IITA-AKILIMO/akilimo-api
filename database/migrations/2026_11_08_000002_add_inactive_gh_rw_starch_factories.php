<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the fresh-root buyers found for Ghana and Rwanda, INACTIVE, with estimated prices per tonne of fresh
 * roots taken from public sources (see docs/data/starch-factories-rw-gh-bi.md). Inactive factories are left
 * out of GET /v1/starch-prices, so nothing computes with these prices until an admin confirms the price and
 * switches the factory on. No buyer was found for Burundi. Existing factories and prices are not overwritten.
 */
return new class extends Migration {
    private const FACTORIES = [
        ['id' => 11, 'name' => 'AyensuStarch', 'label' => 'Ayensu Starch Company (ASCo)', 'country' => 'GH', 'price' => 900, 'currency' => 'GHS'],
        ['id' => 12, 'name' => 'KinaziCassavaPlant', 'label' => 'Kinazi Cassava Plant (KCP)', 'country' => 'RW', 'price' => 330000, 'currency' => 'RWF'],
    ];

    /**
     * @throws Throwable
     */
    public function up(): void
    {
        DB::transaction(function () {
            foreach (self::FACTORIES as $f) {
                $id = DB::table('starch_factories')->where('factory_name', $f['name'])->value('id')
                    ?? DB::table('starch_factories')->insertGetId([
                        // fixed ids, so a new database gives them the same ids as an old one (the seeder gives the others 1 to 10)
                        'id' => $f['id'],
                        'factory_name' => $f['name'],
                        'factory_label' => $f['label'],
                        'country' => $f['country'],
                        'factory_active' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                DB::table('starch_prices')->insertOrIgnore([
                    'starch_factory_id' => $id,
                    'price_class' => 1,
                    'min_starch' => 0,
                    'range_starch' => '>0',
                    'price' => $f['price'],
                    'currency' => $f['currency'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Only remove them while still inactive; a confirmed (activated) factory is kept. Prices cascade.
        DB::table('starch_factories')
            ->whereIn('factory_name', array_column(self::FACTORIES, 'name'))
            ->where('factory_active', false)
            ->delete();
    }
};
