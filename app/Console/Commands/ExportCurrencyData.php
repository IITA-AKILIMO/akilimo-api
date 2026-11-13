<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ResourceBundle;

/**
 * Rebuilds the two data files the currency migrations read, from the ICU data built
 * into PHP (the same data browsers and operating systems use, from Unicode CLDR):
 *
 *  - database/seeders/data/country_currencies.csv  (country code, currency code)
 *  - database/seeders/data/currencies.csv          (code, name, symbol, plural name)
 *
 * A country's currency is the one ICU lists for it with no end date and as legal
 * tender. ICU's data can be older than the world, so the few differences that were
 * checked by hand are the OVERRIDES below and are written into the file. The files
 * are committed; run this only to refresh them, review the diff, then commit.
 *
 *   php artisan currencies:export
 */
class ExportCurrencyData extends Command
{
    protected $signature = 'currencies:export {--dry-run : Print what would change without writing the files}';

    protected $description = 'Rebuild the country-to-currency and currency CSV files from the ICU data in PHP';

    /**
     * Where ICU is out of date or has no entry (null = no currency):
     *  BG  euro since 1 January 2026 (ICU 77 still lists the lev)
     *  AQ  Antarctica has no currency (ICU lists XXX, "no currency")
     *  AN  Netherlands Antilles, dissolved in 2010; the guilder is still shown by the old list
     *  GZ  Gaza Strip, not an ISO country; it uses the Israeli shekel like the West Bank
     *  LS  Lesotho: the rand is also legal tender and ICU lists it first, but the country's own currency is the loti
     */
    private const OVERRIDES = ['BG' => 'EUR', 'AQ' => null, 'AN' => 'ANG', 'GZ' => 'ILS', 'LS' => 'LSL'];

    public function handle(): int
    {
        if (! class_exists(ResourceBundle::class)) {
            $this->error('The PHP intl extension is needed.');

            return self::FAILURE;
        }

        $region = ResourceBundle::create('supplementalData', 'ICUDATA-curr', false);
        $english = ResourceBundle::create('en', 'ICUDATA-curr', false);

        if ($region === null || $english === null) {
            $this->error('The ICU currency data could not be read.');

            return self::FAILURE;
        }

        $current = [];
        foreach ($region['CurrencyMap'] as $code => $entries) {
            foreach ($entries as $entry) {
                if ($entry->get('to') === null && $entry->get('tender') !== false) {
                    $current[(string) $code][] = (string) $entry->get('id');
                }
            }
        }

        // Every country we hold, in code order, with its currency (the first current one ICU lists)
        $rows = [];
        foreach (DB::table('countries')->orderBy('code')->pluck('code') as $code) {
            $currency = array_key_exists($code, self::OVERRIDES) ? self::OVERRIDES[$code] : ($current[$code][0] ?? null);

            if ($currency === 'XXX') {
                $currency = null;
            }

            if ($currency !== null) {
                $rows[$code] = $currency;
            }
        }

        $codes = array_values(array_unique($rows));
        sort($codes);

        $currencies = [];
        foreach ($codes as $code) {
            $names = $english['Currencies'][$code] ?? null;
            $plural = $english['CurrencyPlurals'][$code] ?? null;
            $name = $names === null ? $code : (string) $names->get(1);
            $currencies[] = [
                $code,
                $name,
                $names === null ? $code : (string) $names->get(0),
                $plural === null ? $name : (string) ($plural->get('other') ?? $name),
            ];
        }

        $dir = database_path('seeders/data');
        $this->line(sprintf('%d countries with a currency, %d currencies.', count($rows), count($currencies)));

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        $this->write($dir . '/country_currencies.csv', ['country', 'currency'], array_map(null, array_keys($rows), array_values($rows)));
        $this->write($dir . '/currencies.csv', ['currency', 'name', 'symbol', 'plural'], $currencies);
        $this->info('Written to database/seeders/data. Review the diff, then commit.');

        return self::SUCCESS;
    }

    /**
     * @param list<string> $header
     * @param list<array<int, string>> $rows
     */
    private function write(string $path, array $header, array $rows): void
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, $header, ',', '"', '');

        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }

        fclose($handle);
    }
}
