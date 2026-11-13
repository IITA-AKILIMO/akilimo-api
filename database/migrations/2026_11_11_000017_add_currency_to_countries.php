<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every country gets the currency it uses (countries.currency_code, a foreign key to
 * currencies.currency_code), so the price form can fill the currency in for any
 * country and a price cannot be saved in a currency that does not exist. The
 * currencies table held no link to a country, and several countries share one
 * currency (the euro, the CFA francs, the East Caribbean dollar), so the link
 * is on the country.
 *
 * The pairs are in database/seeders/data/country_currencies.csv, built from the ICU
 * data in PHP by `php artisan currencies:export` (with the few hand-checked
 * overrides listed in that command: Bulgaria on the euro since 1 January 2026, no
 * currency for Antarctica, and the former Netherlands Antilles and Gaza). The
 * owner should check any country that matters to a price; the five supported
 * countries are NGN, TZS, RWF, GHS and BIF. Countries that already have a currency
 * are left alone.
 */
return new class extends Migration {
    public function up(): void
    {
        // The code is already unique in practice; the index makes it a valid foreign key target
        Schema::table('currencies', function (Blueprint $table) {
            $table->unique('currency_code');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->string('currency_code', 3)->nullable()->after('name');
        });

        $handle = fopen(database_path('seeders/data/country_currencies.csv'), 'r');
        fgetcsv($handle, 0, ',', '"', '');

        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            [$country, $currency] = $line;
            DB::table('countries')->where('code', $country)->whereNull('currency_code')->update(['currency_code' => $currency]);
        }

        fclose($handle);

        Schema::table('countries', function (Blueprint $table) {
            $table->foreign('currency_code')->references('currency_code')->on('currencies')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropForeign(['currency_code']);
            $table->dropColumn('currency_code');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->dropUnique(['currency_code']);
        });
    }
};
