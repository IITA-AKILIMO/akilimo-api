<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The investment amount prices (BI, GH, NG, TZ) were backfilled without a currency,
 * because the lookup by country found none (see the previous migration). Each is
 * set to its country's currency, with a history entry; the price is unchanged.
 * Rolling back clears the currency again and adds a rolled_back entry.
 */
return new class extends Migration {
    private const CURRENCIES = ['NG' => 'NGN', 'TZ' => 'TZS', 'RW' => 'RWF', 'GH' => 'GHS', 'BI' => 'BIF'];

    private const NOTE = 'Currency filled in from the country; the price is unchanged.';

    public function up(): void
    {
        foreach (DB::table('default_prices')->where(fn ($q) => $q->whereNull('currency')->orWhere('currency', ''))->get() as $row) {
            $currency = self::CURRENCIES[$row->country] ?? null;

            if ($currency === null) {
                continue;
            }

            DB::table('default_prices')->where('id', $row->id)->update(['currency' => $currency, 'updated_at' => now()]);

            DB::table('default_price_history')->insert([
                'default_price_id' => $row->id,
                'country' => $row->country,
                'item' => $row->item,
                'action' => 'updated',
                'old_values' => json_encode(['currency' => $row->currency]),
                'new_values' => json_encode(['currency' => $currency]),
                'note' => self::NOTE,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('default_price_history')->where('action', 'updated')->where('note', self::NOTE)->get() as $entry) {
            $new = json_decode((string) $entry->new_values, true);
            $old = json_decode((string) $entry->old_values, true);
            $price = $entry->default_price_id === null ? null : DB::table('default_prices')->where('id', $entry->default_price_id)->first();

            // Only a price still carrying the currency this migration gave it
            if ($price === null || $price->currency !== ($new['currency'] ?? null)) {
                continue;
            }

            DB::table('default_prices')->where('id', $price->id)->update(['currency' => $old['currency'] ?? null]);

            DB::table('default_price_history')->insert([
                'default_price_id' => $price->id,
                'country' => $price->country,
                'item' => $price->item,
                'action' => 'rolled_back',
                'old_values' => $entry->new_values,
                'new_values' => $entry->old_values,
                'note' => 'Currency restored by rolling back the migration that filled it in.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
