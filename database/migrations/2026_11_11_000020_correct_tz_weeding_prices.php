<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tanzanian weeding was 600,000 and 450,000 per acre in a database restored from an older dump: ten times
 * the right values, which are 60,000 and 45,000 (docs/data/labour-costs-rw-gh-bi.md, "Resolved
 * 2026-11-08": the API seed file and the compute service agree on them). A database that was seeded after
 * that fix already has them; this corrects one that was not.
 *
 * Only a row that still holds the wrong value is changed, with a history entry. Rolling back puts the wrong
 * value back and adds a rolled_back entry, and the history stays.
 */
return new class extends Migration {
    private const NOTE = 'Tanzanian weeding was ten times too high; corrected to the value the compute service uses.';

    /** item => [wrong, right] */
    private const FIXES = ['weeding1' => [600000, 60000], 'weeding2' => [450000, 45000]];

    public function up(): void
    {
        foreach (self::FIXES as $item => [$wrong, $right]) {
            $row = DB::table('price_catalogue')->where('country', 'TZ')->where('item', $item)->first();

            if ($row === null || (float) $row->price !== (float) $wrong) {
                continue;
            }

            DB::table('price_catalogue')->where('id', $row->id)->update(['price' => $right, 'updated_at' => now()]);
            $this->history($row->id, $item, 'updated', $wrong, $right, self::NOTE);
        }
    }

    public function down(): void
    {
        foreach (self::FIXES as $item => [$wrong, $right]) {
            $row = DB::table('price_catalogue')->where('country', 'TZ')->where('item', $item)->first();

            if ($row === null || (float) $row->price !== (float) $right) {
                continue;
            }

            DB::table('price_catalogue')->where('id', $row->id)->update(['price' => $wrong, 'updated_at' => now()]);
            $this->history($row->id, $item, 'rolled_back', $right, $wrong, 'Restored by rolling back the weeding price correction.');
        }
    }

    private function history(int $id, string $item, string $action, float|int $from, float|int $to, string $note): void
    {
        DB::table('price_catalogue_history')->insert([
            'price_catalogue_id' => $id,
            'country' => 'TZ',
            'item' => $item,
            'action' => $action,
            'old_values' => json_encode(['price' => $from]),
            'new_values' => json_encode(['price' => $to]),
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
