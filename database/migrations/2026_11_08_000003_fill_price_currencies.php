<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Default and starch prices were seeded without a currency (Ghana's carried "GHC", the cedi code retired in
 * 2007). Every price is in its country's own currency, so the ISO code follows from the country; prices are
 * unchanged. Rows that already have another currency are left alone. Safe to run more than once.
 */
return new class extends Migration {
    private const CURRENCIES = ['NG' => 'NGN', 'TZ' => 'TZS', 'RW' => 'RWF', 'GH' => 'GHS', 'BI' => 'BIF'];

    /**
     * @throws Throwable
     */
    public function up(): void
    {
        DB::transaction(function () {
            foreach (self::CURRENCIES as $country => $currency) {
                DB::table('default_prices')
                    ->where('country', $country)
                    ->where(fn ($q) => $q->whereNull('currency')->orWhereIn('currency', ['', 'GHC']))
                    ->update(['currency' => $currency]);

                DB::table('starch_prices')
                    ->whereIn('starch_factory_id', DB::table('starch_factories')->where('country', $country)->select('id'))
                    ->where(fn ($q) => $q->whereNull('currency')->orWhereIn('currency', ['', 'GHC']))
                    ->update(['currency' => $currency]);
            }
        });
    }

    public function down(): void
    {
        // Not reversed: the rows had no currency (or an obsolete one) before
    }
};
