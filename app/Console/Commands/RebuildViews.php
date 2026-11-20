<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Console\Commands;

use App\Support\ViewRebuilder;
use Illuminate\Console\Command;

/**
 * Creates or replaces every database view from database/views/*.sql, so a view follows a table that gained or
 * lost a column. It runs by itself after every migration; run it by hand after editing a view file.
 *
 *   php artisan views:rebuild            create or replace every view from its file
 *   php artisan views:rebuild --check    only say which views are missing or no longer work
 */
class RebuildViews extends Command
{
    protected $signature = 'views:rebuild
                            {--check : Only report views that are missing or no longer work; change nothing}';

    protected $description = 'Create or replace every database view from the files in database/views';

    public function handle(ViewRebuilder $views): int
    {
        if ($views->definitions() === []) {
            $this->warn('There are no view files in ' . $views->directory());

            return self::SUCCESS;
        }

        return $this->option('check') ? $this->check($views) : $this->rebuild($views);
    }

    private function rebuild(ViewRebuilder $views): int
    {
        $result = $views->rebuild();

        foreach ($result['rebuilt'] as $name) {
            $this->line("  <info>rebuilt</info> {$name}");
        }

        foreach ($result['failed'] as $name => $error) {
            $this->line("  <error>failed</error>  {$name}: " . $this->short($error));
        }

        if ($result['failed'] !== []) {
            $this->newLine();
            $this->error(count($result['failed']) . ' view(s) could not be rebuilt; each one that exists was left as it was.');

            return self::FAILURE;
        }

        $this->info(count($result['rebuilt']) . ' view(s) rebuilt.');

        return self::SUCCESS;
    }

    private function check(ViewRebuilder $views): int
    {
        $report = $views->check();
        $problems = 0;

        foreach ($report['missing'] as $name) {
            $this->line("  <error>missing</error>    {$name} has a file but no view");
            $problems++;
        }

        foreach ($report['broken'] as $name => $error) {
            $this->line("  <error>broken</error>     {$name}: " . $this->short($error));
            $problems++;
        }

        foreach ($report['unknown'] as $name) {
            $this->line("  <comment>no file</comment>    {$name} is a vw_ view without a file in database/views");
            $problems++;
        }

        foreach ($report['unprefixed'] as $name) {
            $this->line("  <comment>no prefix</comment>  {$name} is a view that does not start with " . ViewRebuilder::PREFIX);
            $problems++;
        }

        if ($problems === 0) {
            $this->info('Every view exists, works, and has a file.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn("{$problems} problem(s). Run `php artisan views:rebuild` to recreate the views from their files.");

        return self::FAILURE;
    }

    private function short(string $message): string
    {
        $first = strtok($message, "\n") ?: $message;

        return mb_strlen($first) > 220 ? mb_substr($first, 0, 217) . '...' : $first;
    }
}
