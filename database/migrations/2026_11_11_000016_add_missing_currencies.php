<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The currencies table had 119 currencies, several withdrawn (HRK, EEK, LTL, LVL,
 * VEF, ZMK) and none for many countries (the East Caribbean dollar, the CFP franc,
 * the Zambian kwacha, ...). This adds the current ones that were missing, from
 * database/seeders/data/currencies.csv (built from the ICU data in PHP by
 * `php artisan currencies:export`), so the next migration can give every country a
 * currency. Withdrawn ones are left in place: nothing is deleted. A code that
 * exists is left alone.
 *
 * Added rows are stamped with ADDED_AT, so the rollback removes exactly those, and
 * only if no price or country uses them.
 */
return new class extends Migration {
    private const ADDED_AT = '2026-11-11 00:00:00';

    public function up(): void
    {
        foreach ($this->rows() as $row) {
            if (DB::table('currencies')->where('currency_code', $row['currency'])->exists()) {
                continue;
            }

            DB::table('currencies')->insert([
                'currency_name' => $row['name'],
                'currency_code' => $row['currency'],
                'currency_symbol' => $row['symbol'],
                'currency_native_symbol' => $row['symbol'],
                'name_plural' => $row['plural'],
                'created_at' => self::ADDED_AT,
                'updated_at' => self::ADDED_AT,
            ]);
        }
    }

    public function down(): void
    {
        foreach ($this->rows() as $row) {
            $code = $row['currency'];
            $used = DB::table('default_prices')->where('currency', $code)->exists()
                || DB::table('starch_prices')->where('currency', $code)->exists()
                || (Schema::hasColumn('countries', 'currency_code') && DB::table('countries')->where('currency_code', $code)->exists());

            if (! $used) {
                DB::table('currencies')->where('currency_code', $code)->where('created_at', self::ADDED_AT)->delete();
            }
        }
    }

    /**
     * @return list<array{currency: string, name: string, symbol: string, plural: string}>
     */
    private function rows(): array
    {
        $handle = fopen(database_path('seeders/data/currencies.csv'), 'r');
        $header = fgetcsv($handle, 0, ',', '"', '');
        $rows = [];

        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $rows[] = array_combine($header, $line);
        }

        fclose($handle);

        return $rows;
    }
};
