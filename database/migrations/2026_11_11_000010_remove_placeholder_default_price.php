<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Removes a test row that was saved through the API documentation's "try it" form
 * with its placeholder values: country "st", item "string", unit "string",
 * currency "str", price 0, and active, so it was being served. Only a row that
 * still has exactly those placeholder values goes.
 *
 * The history keeps the whole row (the delete is recorded as an entry), and
 * rolling back puts the row back with the same id and adds a rolled_back entry.
 */
return new class extends Migration {
    private const NOTE = 'Placeholder test row ("string") removed.';

    public function up(): void
    {
        $rows = DB::table('default_prices')
            ->where('item', 'string')->where('unit', 'string')->where('currency', 'str')->where('price', 0)
            ->get();

        foreach ($rows as $row) {
            DB::table('default_price_history')->insert([
                'default_price_id' => null,
                'country' => $row->country,
                'item' => $row->item,
                'action' => 'deleted',
                'old_values' => json_encode((array) $row),
                'new_values' => null,
                'note' => self::NOTE,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('default_prices')->where('id', $row->id)->delete();
        }
    }

    public function down(): void
    {
        $entries = DB::table('default_price_history')->where('action', 'deleted')->where('note', self::NOTE)->get();

        foreach ($entries as $entry) {
            $row = json_decode((string) $entry->old_values, true);

            // Put it back only if its id and (country, item) are free
            if (! is_array($row) || DB::table('default_prices')->where('id', $row['id'])->exists()
                || DB::table('default_prices')->where('country', $row['country'])->where('item', $row['item'])->exists()) {
                continue;
            }

            DB::table('default_prices')->insert($row);

            DB::table('default_price_history')->insert([
                'default_price_id' => $row['id'],
                'country' => $row['country'],
                'item' => $row['item'],
                'action' => 'rolled_back',
                'old_values' => null,
                'new_values' => json_encode($row),
                'note' => 'Placeholder test row restored by rolling back its removal.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
