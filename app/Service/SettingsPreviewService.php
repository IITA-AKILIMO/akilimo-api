<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Support\PriceLists;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Shows what a change to a price rule (the steps, the rounding, the units, the
 * investment multiples, the operation mapping) would do to the lists the app
 * reads, before it is saved.
 *
 * The lists are database views over the catalogue and the rules, so the exact
 * answer is to make the change, read the lists, and take the change back. That
 * is what this does, inside a transaction that is always rolled back, so the
 * preview uses the real logic and cannot disagree with saving.
 */
class SettingsPreviewService
{
    /** Changed rows shown per list; the count of all of them is still reported. */
    private const SAMPLE = 40;

    /**
     * @param list<string> $lists keys of PriceLists::LISTS to compare
     * @param Closure(): void $change the write that saving would make
     * @return array{lists: list<array<string, mixed>>, changed: int}
     */
    public function compare(array $lists, Closure $change): array
    {
        DB::beginTransaction();

        try {
            $before = $this->snapshot($lists);
            $change();
            $after = $this->snapshot($lists);
        } finally {
            DB::rollBack();
        }

        $report = [];
        $total = 0;

        foreach ($lists as $list) {
            $entry = $this->diff($list, $before[$list], $after[$list]);
            $total += $entry['changed'] + $entry['added'] + $entry['removed'];
            $report[] = $entry;
        }

        return ['lists' => $report, 'changed' => $total];
    }

    /**
     * @param list<string> $lists
     * @return array<string, array<int, array{label: string, value: string, country: string}>>
     */
    private function snapshot(array $lists): array
    {
        $snapshot = [];

        foreach ($lists as $list) {
            $def = PriceLists::LISTS[$list];
            $query = DB::table($def['table'])->orderBy($def['country']);

            foreach ($def['order'] as $column) {
                $query->orderBy($column);
            }

            $snapshot[$list] = [];

            foreach ($query->get() as $row) {
                $snapshot[$list][(int) $row->id] = PriceLists::describe($list, $row) + ['country' => (string) $row->{$def['country']}];
            }
        }

        return $snapshot;
    }

    /**
     * @param array<int, array{label: string, value: string, country: string}> $before
     * @param array<int, array{label: string, value: string, country: string}> $after
     * @return array<string, mixed>
     */
    private function diff(string $list, array $before, array $after): array
    {
        $rows = [];
        $changed = 0;
        $added = 0;
        $removed = 0;

        foreach ($after as $id => $row) {
            if (! isset($before[$id])) {
                $added++;
                $rows[] = ['label' => $row['label'], 'before' => null, 'after' => $row['value']];
            } elseif ($before[$id]['value'] !== $row['value']) {
                $changed++;
                $rows[] = ['label' => $row['label'], 'before' => $before[$id]['value'], 'after' => $row['value']];
            }
        }

        foreach ($before as $id => $row) {
            if (! isset($after[$id])) {
                $removed++;
                $rows[] = ['label' => $row['label'], 'before' => $row['value'], 'after' => null];
            }
        }

        return [
            'key' => $list,
            'title' => PriceLists::LISTS[$list]['title'],
            'total' => count($after),
            'changed' => $changed,
            'added' => $added,
            'removed' => $removed,
            'rows' => array_slice($rows, 0, self::SAMPLE),
            'truncated' => count($rows) > self::SAMPLE,
        ];
    }
}
