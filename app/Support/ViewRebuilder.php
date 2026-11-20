<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Creates the database views from the files in database/views (one file per view, named for the view, such
 * as vw_fertilizers.sql). The files are the definition of the views: a view is changed by editing its file,
 * never by a migration, and a column added to or dropped from a table a view reads is followed by a rebuild
 * (it runs by itself after every migration; see AppServiceProvider and `php artisan views:rebuild`).
 *
 * Each view is made with CREATE OR REPLACE VIEW, so a definition that fails leaves the view that exists as it
 * was; nothing is dropped first. A view may read another view, so they are tried until all exist.
 */
class ViewRebuilder
{
    public const PREFIX = 'vw_';

    public function __construct(private readonly ?string $directory = null) {}

    public function directory(): string
    {
        return $this->directory ?? database_path('views');
    }

    /**
     * The definitions, by view name: the SQL of each file with its comment lines taken out.
     *
     * @return array<string, string>
     */
    public function definitions(): array
    {
        $definitions = [];
        $files = glob($this->directory() . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        sort($files);

        foreach ($files as $file) {
            $sql = trim((string) preg_replace('/^\s*--.*$/m', '', (string) file_get_contents($file)));

            if ($sql !== '') {
                $definitions[pathinfo($file, PATHINFO_FILENAME)] = $sql;
            }
        }

        return $definitions;
    }

    /**
     * Whether any view already has the prefix. Before the migration that gives the views their names, and
     * after it is rolled back, the views have their old names and there is nothing to rebuild.
     */
    public function hasPrefixedViews(): bool
    {
        return $this->existingViews() !== [] && collect($this->existingViews())->contains(fn (string $name) => str_starts_with($name, self::PREFIX));
    }

    /**
     * @return list<string>
     */
    public function existingViews(): array
    {
        return array_map(
            fn ($row) => (string) $row->name,
            DB::select('SELECT table_name AS name FROM information_schema.views WHERE table_schema = DATABASE() ORDER BY table_name'),
        );
    }

    /**
     * Create or replace every view from its file.
     *
     * @return array{rebuilt: list<string>, failed: array<string, string>} the names made, and the error for each that could not be
     */
    public function rebuild(): array
    {
        $pending = $this->definitions();
        $rebuilt = [];
        $errors = [];

        while ($pending !== []) {
            $progress = false;

            foreach ($pending as $name => $sql) {
                try {
                    DB::unprepared($sql);
                    $rebuilt[] = $name;
                    unset($pending[$name], $errors[$name]);
                    $progress = true;
                } catch (Throwable $e) {
                    // it may read a view that is not made yet; the next pass tries again
                    $errors[$name] = $e->getMessage();
                }
            }

            if (! $progress) {
                break;
            }
        }

        return ['rebuilt' => $rebuilt, 'failed' => array_intersect_key($errors, $pending)];
    }

    /**
     * What is wrong with the views, changing nothing.
     *
     * @return array{missing: list<string>, broken: array<string, string>, unknown: list<string>, unprefixed: list<string>}
     *         missing: has a file, no view; broken: the view no longer works (a table or column it reads is gone);
     *         unknown: a vw_ view with no file; unprefixed: a view without the vw_ prefix
     */
    public function check(): array
    {
        $files = array_keys($this->definitions());
        $existing = $this->existingViews();
        $broken = [];

        foreach (array_intersect($files, $existing) as $name) {
            try {
                DB::select("SELECT * FROM `{$name}` LIMIT 0");
            } catch (Throwable $e) {
                $broken[$name] = $e->getMessage();
            }
        }

        return [
            'missing' => array_values(array_diff($files, $existing)),
            'broken' => $broken,
            'unknown' => array_values(array_filter(array_diff($existing, $files), fn (string $name) => str_starts_with($name, self::PREFIX))),
            'unprefixed' => array_values(array_filter($existing, fn (string $name) => ! str_starts_with($name, self::PREFIX))),
        ];
    }
}
