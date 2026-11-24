<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Every database view starts with vw_, so a view cannot be mistaken for a table (it is read-only, and it
 * is computed from other tables): fertilizers is vw_fertilizers, fertilizer_prices is vw_fertilizer_prices,
 * and so on for the eight views the app lists and the price lists are built from. The columns, and so the
 * API responses, are unchanged.
 *
 * MariaDB does not rewrite a view that reads another view when that one is renamed, so each view is taken
 * from its live definition, the references to the other views are changed to the new names (only where
 * they name a table, never a column such as investment_amount), and all eight are dropped and created
 * again. The same runs backwards on rollback.
 *
 * A new view is named with the prefix from now on (see tests/Feature/ViewNamingTest.php).
 */
return new class extends Migration {
    /** old name => new name */
    private const VIEWS = [
        'fertilizers' => 'vw_fertilizers',
        'fertilizer_prices' => 'vw_fertilizer_prices',
        'cassava_prices' => 'vw_cassava_prices',
        'potato_prices' => 'vw_potato_prices',
        'maize_prices' => 'vw_maize_prices',
        'investment_amount' => 'vw_investment_amount',
        'operation_costs' => 'vw_operation_costs',
        'price_bands' => 'vw_price_bands',
    ];

    public function up(): void
    {
        $this->rename(self::VIEWS);
    }

    public function down(): void
    {
        $this->rename(array_flip(self::VIEWS));
    }

    /**
     * @param array<string, string> $names old name => new name
     */
    private function rename(array $names): void
    {
        $schema = DB::getDatabaseName();
        $definitions = [];

        foreach ($names as $old => $new) {
            $row = DB::selectOne('SELECT COUNT(*) AS n FROM information_schema.views WHERE table_schema = ? AND table_name = ?', [$schema, $old]);

            if ((int) $row->n === 0) {
                continue;
            }

            $sql = (string) ((array) DB::selectOne("SHOW CREATE VIEW `{$old}`"))['Create View'];

            // Another view is named after FROM or JOIN (through any opening brackets), with or without the
            // schema in front; a column or an alias of the same name (investment_amount) is never changed
            foreach ($names as $from => $to) {
                $sql = (string) preg_replace(
                    '/(\b(?:from|join)\s*\(*\s*(?:`' . preg_quote($schema, '/') . '`\.)?)`' . preg_quote($from, '/') . '`/i',
                    '$1`' . $to . '`',
                    $sql,
                );
            }

            $sql = (string) preg_replace('/\bVIEW `' . preg_quote($old, '/') . '`/', "VIEW `{$new}`", $sql, 1);
            $definitions[$old] = $sql;
        }

        foreach (array_keys($definitions) as $old) {
            DB::statement("DROP VIEW IF EXISTS `{$old}`");
        }

        $pending = $definitions;
        $lastError = [];

        // A view may read another one; keep creating until all exist (the order is not known up front)
        while ($pending !== []) {
            $created = false;

            foreach ($pending as $old => $sql) {
                try {
                    DB::statement($sql);
                    unset($pending[$old]);
                    $created = true;
                } catch (Throwable $e) {
                    // the view it reads is not back yet; try again on the next pass
                    $lastError[$old] = $e->getMessage();
                }
            }

            if (! $created) {
                throw new RuntimeException('Could not recreate the views: ' . implode('; ', array_map(
                    fn ($name) => $name . ' (' . ($lastError[$name] ?? '?') . ')',
                    array_keys($pending),
                )));
            }
        }
    }
};
