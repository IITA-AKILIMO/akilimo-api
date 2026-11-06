<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Models\TrafficExclusion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Marks api_requests as staff or test traffic (`excluded`) from the rules in traffic_exclusions,
 * so the dashboard and reports count real users only. The rules become one SQL condition, used to
 * set the flag on a new request and to recompute it for every request when the rules change.
 */
class TrafficExclusionService
{
    /** What a rule checks, with a label for the admin page. */
    public const FIELDS = [
        'phone' => 'Phone number',
        'name' => 'Name',
        'device' => 'Device ID',
    ];

    public const MATCH_TYPES = [
        'exact' => 'is exactly',
        'starts_with' => 'starts with',
        'contains' => 'contains',
        'regex' => 'matches pattern',
    ];

    // Phones are compared as digits only, so "+254 700…", "254700…" and "254-700…" are the same.
    private const PHONE_DIGITS = "REGEXP_REPLACE(COALESCE(phone_number, ''), '[^0-9]', '')";

    // The old app sent a full name (generated `full_names`); the current one also sends a user name.
    private const NAME_EXPRS = [
        "LOWER(COALESCE(full_names, ''))",
        "LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(droid_request, '$.user_info.user_name')), ''))",
    ];

    private const DEVICE = "LOWER(COALESCE(device_token, ''))";

    /**
     * Recompute `excluded` for every request, or for one. Returns the number of rows that changed.
     */
    public function refresh(?int $requestId = null): int
    {
        [$condition, $bindings] = $this->conditionSql(TrafficExclusion::active()->get());
        $flag = "(CASE WHEN {$condition} THEN 1 ELSE 0 END)";

        $sql = "UPDATE api_requests SET excluded = {$flag} WHERE excluded <> {$flag}";
        $params = [...$bindings, ...$bindings];
        if ($requestId !== null) {
            $sql .= ' AND id = ?';
            $params[] = $requestId;
        }

        return DB::update($sql, $params);
    }

    /**
     * How many requests each rule matches, whether or not it is switched on, in one pass
     * over the table.
     *
     * @param  Collection<int, TrafficExclusion>  $rules
     * @return array<int, int> rule id => count
     */
    public function matchCounts(Collection $rules): array
    {
        if ($rules->isEmpty()) {
            return [];
        }

        $selects = [];
        $bindings = [];
        foreach ($rules as $rule) {
            [$sql, $ruleBindings] = $this->ruleSql($rule);
            $selects[] = "SUM(CASE WHEN {$sql} THEN 1 ELSE 0 END) AS r{$rule->id}";
            array_push($bindings, ...$ruleBindings);
        }

        $row = (array) DB::selectOne('SELECT '.implode(', ', $selects).' FROM api_requests', $bindings);

        return $rules->mapWithKeys(fn ($r) => [$r->id => (int) ($row["r{$r->id}"] ?? 0)])->all();
    }

    /**
     * Whether MariaDB accepts a pattern, so a bad one is refused when saved rather than
     * breaking every later update.
     */
    public static function isValidPattern(string $pattern): bool
    {
        try {
            DB::selectOne("SELECT '' REGEXP ? AS ok", [$pattern]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * The rules as one condition (any rule matches), with its bindings.
     *
     * @param  Collection<int, TrafficExclusion>  $rules
     * @return array{0: string, 1: array<int, string>}
     */
    public function conditionSql(Collection $rules): array
    {
        if ($rules->isEmpty()) {
            return ['0 = 1', []];
        }

        $parts = [];
        $bindings = [];
        foreach ($rules as $rule) {
            [$sql, $ruleBindings] = $this->ruleSql($rule);
            $parts[] = "({$sql})";
            array_push($bindings, ...$ruleBindings);
        }

        return [implode(' OR ', $parts), $bindings];
    }

    /**
     * One rule as SQL against api_requests.
     *
     * @return array{0: string, 1: array<int, string>}
     */
    public function ruleSql(TrafficExclusion $rule): array
    {
        $exprs = match ($rule->field) {
            'phone' => [$rule->match_type === 'regex' ? "COALESCE(phone_number, '')" : self::PHONE_DIGITS],
            'name' => self::NAME_EXPRS,
            'device' => [self::DEVICE],
            default => [],
        };
        if ($exprs === []) {
            return ['0 = 1', []];
        }

        $value = $rule->value;
        if ($rule->field === 'phone' && $rule->match_type !== 'regex') {
            $value = preg_replace('/\D+/', '', $value);
        } elseif ($rule->match_type !== 'regex') {
            $value = mb_strtolower($value);
        }
        if ($value === '') {
            return ['0 = 1', []];
        }

        [$op, $operand] = match ($rule->match_type) {
            'exact' => ['=', $value],
            'starts_with' => ['LIKE', $this->escapeLike($value).'%'],
            'contains' => ['LIKE', '%'.$this->escapeLike($value).'%'],
            // Names and device IDs are matched ignoring case, like the other match types
            'regex' => ['REGEXP', $rule->field === 'phone' ? $value : '(?i)'.$value],
            default => [null, null],
        };
        if ($op === null) {
            return ['0 = 1', []];
        }

        $sql = implode(' OR ', array_map(fn ($expr) => "{$expr} {$op} ?", $exprs));

        return [$sql, array_fill(0, count($exprs), $operand)];
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
