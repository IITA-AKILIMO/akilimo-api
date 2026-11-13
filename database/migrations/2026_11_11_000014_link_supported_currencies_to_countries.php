<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * None of the 119 currencies said which country uses it (country_code was empty),
 * so choosing a country in the price form could not fill in the currency, and a
 * migration that looked the currency up by country found nothing. The currencies
 * the five supported countries use are linked to them here: NGN to NG, TZS to TZ,
 * RWF to RW, GHS to GH, BIF to BI. Each of these is used by one country only. A
 * row that already names a country is left alone.
 */
return new class extends Migration {
    private const COUNTRIES = [
        'NGN' => ['NG', 'Nigeria'],
        'TZS' => ['TZ', 'Tanzania'],
        'RWF' => ['RW', 'Rwanda'],
        'GHS' => ['GH', 'Ghana'],
        'BIF' => ['BI', 'Burundi'],
    ];

    public function up(): void
    {
        foreach (self::COUNTRIES as $currency => [$code, $name]) {
            DB::table('currencies')
                ->where('currency_code', $currency)
                ->whereNull('country_code')
                ->update(['country_code' => $code, 'country' => $name]);
        }
    }

    public function down(): void
    {
        // Only a row still as this migration set it
        foreach (self::COUNTRIES as $currency => [$code, $name]) {
            DB::table('currencies')
                ->where('currency_code', $currency)
                ->where('country_code', $code)
                ->where('country', $name)
                ->update(['country_code' => null, 'country' => null]);
        }
    }
};
