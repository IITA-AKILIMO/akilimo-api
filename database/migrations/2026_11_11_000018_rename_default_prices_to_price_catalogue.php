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
 * default_prices becomes price_catalogue: it holds the whole price catalogue (fertilizers, produce,
 * labour, machinery and the investment amount), not only "defaults". Rows, ids, keys, foreign keys
 * (default_price_history.default_price_id follows the rename) and history are unchanged.
 *
 * MariaDB does not rewrite a view when a table it reads is renamed, so every view that depends on the
 * table, directly or through another view (fertilizer_prices, price_bands, cassava_prices, ...), is taken
 * from its live definition, dropped, and created again against the new name. The same runs backwards on
 * rollback. The history table and the public API paths (/v1/default-prices) keep their names.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->rename('default_prices', 'price_catalogue');
    }

    public function down(): void
    {
        $this->rename('price_catalogue', 'default_prices');
    }

    private function rename(string $from, string $to): void
    {
        $definitions = $this->dependentViews($from);

        if ($definitions !== []) {
            DB::statement('DROP VIEW IF EXISTS `' . implode('`, `', array_keys($definitions)) . '`');
        }

        Schema::rename($from, $to);

        $pending = array_map(fn (string $sql) => str_replace("`{$from}`", "`{$to}`", $sql), $definitions);

        // A view may read another one; keep creating until all exist (the order is not known up front)
        while ($pending !== []) {
            $created = false;

            foreach ($pending as $name => $sql) {
                try {
                    DB::statement($sql);
                    unset($pending[$name]);
                    $created = true;
                } catch (Throwable) {
                    // its dependency is not back yet; try again on the next pass
                }
            }

            if (! $created) {
                throw new RuntimeException('Could not recreate the views: ' . implode(', ', array_keys($pending)));
            }
        }
    }

    /**
     * The views that read the table, or read a view that does, with their definitions.
     *
     * @return array<string, string> view name => CREATE VIEW statement
     */
    private function dependentViews(string $table): array
    {
        $all = [];

        foreach (DB::select('SELECT table_name AS name FROM information_schema.views WHERE table_schema = DATABASE()') as $view) {
            $row = (array) DB::selectOne('SHOW CREATE VIEW `' . $view->name . '`');
            $all[(string) $view->name] = (string) $row['Create View'];
        }

        $affected = [];
        $changed = true;

        while ($changed) {
            $changed = false;

            foreach ($all as $name => $sql) {
                if (isset($affected[$name])) {
                    continue;
                }

                $reads = str_contains($sql, "`{$table}`");

                foreach (array_keys($affected) as $other) {
                    $reads = $reads || str_contains($sql, "`{$other}`");
                }

                if ($reads) {
                    $affected[$name] = $sql;
                    $changed = true;
                }
            }
        }

        return $affected;
    }
};
