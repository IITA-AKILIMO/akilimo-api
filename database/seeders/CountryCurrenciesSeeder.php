<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Gives each country the currency it uses (country_currencies.csv). The countries are seeded after the
 * migration that adds the column, so the link is made here. A country that already has a currency is left
 * as it is, and a currency that does not exist is skipped.
 */
class CountryCurrenciesSeeder extends Seeder
{
    use ReadsSeedCsv;

    public function run(): void
    {
        $known = DB::table('currencies')->pluck('currency_code')->all();

        foreach ($this->csvRows('country_currencies.csv') as $row) {
            if (! in_array($row['currency'], $known, true)) {
                continue;
            }

            DB::table('countries')->where('code', $row['country'])->whereNull('currency_code')->update(['currency_code' => $row['currency']]);
        }
    }
}
