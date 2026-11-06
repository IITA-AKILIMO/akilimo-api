<?php

namespace App\Repositories;

use App\Models\ApiKey;
use App\Models\Base\ApiRequest;
use App\Models\Base\Country;
use App\Models\Base\Fertilizer;
use App\Models\Base\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @extends BaseRepo<ApiRequest>
 */
class ApiRequestStatsRepo extends BaseRepo
{
    protected function model(): string
    {
        return ApiRequest::class;
    }

    /**
     * Get dashboard stats with customizable day intervals.
     *
     * @param  int  $chartDays  Days for chart data (default 30)
     * @param  int  $metricsDays  Days for metrics calculations (default 7)
     * @param  int  $cacheMinutes  Cache duration in minutes (default 15)
     */
    /** Price lists older than this are flagged on the dashboard. */
    public const STALE_AFTER_DAYS = 90;

    /** Sections only admins see (internal error text, data they alone can fix). */
    public const ADMIN_ONLY_KEYS = ['failures', 'freshness'];

    public function getDashboardStats(
        int $chartDays = 30,
        int $metricsDays = 7,
        int $cacheMinutes = 15
    ): array {
        return Cache::remember($this->cacheKey($chartDays, $metricsDays), $cacheMinutes * 60, function () use ($chartDays, $metricsDays) {
            return [
                'chartDays' => $chartDays,
                'stats' => $this->getSummaryStats(),
                'charts' => $this->getChartData($chartDays),
                'metrics' => $this->getMetrics($metricsDays),
                'recentRequests' => $this->getRecentRequests(),
                'feedback' => $this->getFeedbackStats($metricsDays),
                'failures' => $this->getRecentFailures(),
                'freshness' => $this->getDataFreshness(),
            ];
        });
    }

    /**
     * Force refresh stats (clears cache).
     */
    public function refreshDashboardStats(int $chartDays = 30, int $metricsDays = 7): array
    {
        Cache::forget($this->cacheKey($chartDays, $metricsDays));

        return $this->getDashboardStats($chartDays, $metricsDays);
    }

    // v2: the payload shape changed; old cached arrays must not be served to the new page.
    protected function cacheKey(int $chartDays, int $metricsDays): string
    {
        return "dashboard_stats_v2_{$chartDays}_{$metricsDays}";
    }

    /**
     * Requests from real users: `excluded` marks staff and test traffic
     * (see BackfillAppReport), which would skew reach and quality figures.
     */
    protected function userRequests(int $days): Builder
    {
        return DB::table('api_requests')
            ->where('created_at', '>=', now()->subDays($days))
            ->where(fn ($q) => $q->where('excluded', false)->orWhereNull('excluded'));
    }

    // Failed computes store a body with an `error` key (RecommendationService).
    protected const FAILED_SQL = "JSON_EXTRACT(plumber_response, '$.error') IS NOT NULL";

    // The column defaults to '{}' until the compute answer (or error) is saved.
    protected const ANSWERED_SQL = "plumber_response IS NOT NULL AND plumber_response NOT IN ('{}', '[]', '')";

    protected function getSummaryStats(): array
    {
        return [
            // Count directly on the source table — avoids the view and its expressions
            'totalRequests' => (int) DB::table('api_requests')->count(),
            'totalUsers' => (int) User::count(),
            'activeKeys' => (int) ApiKey::where('is_active', true)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->count(),
            'totalFertilizers' => (int) Fertilizer::where('available', true)->count(),
            'totalCountries' => (int) Country::where('active', true)->count(),
        ];
    }

    protected function getChartData(int $days = 30): array
    {
        $since = now()->subDays($days);

        $dailyRequests = $this->model
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $since)
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'count' => (int) $row->count,
            ]);

        $byUseCase = $this->model
            ->selectRaw('use_case, COUNT(*) as count')
            ->where('created_at', '>=', $since)
            ->where('use_case', '!=', 'NA')
            ->groupBy('use_case')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'use_case' => $row->use_case,
                'count' => (int) $row->count,
            ]);

        $byCountry = $this->userRequests($days)
            ->selectRaw('country_code, COUNT(*) as count')
            ->whereNotNull('country_code')
            ->where('country_code', '!=', '')
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'country_code' => $row->country_code,
                'count' => (int) $row->count,
            ]);

        // Older rows have gender in the column (free text, English and Swahili);
        // newer ones only in the request's user_info (M/F). The MCP server sends a
        // placeholder "M" for its anonymous user, which is not a real answer.
        $gender = "LOWER(COALESCE(NULLIF(gender, ''),
            JSON_UNQUOTE(JSON_EXTRACT(droid_request, '$.user_info.gender')),
            JSON_UNQUOTE(JSON_EXTRACT(droid_request, '$.userInfo.gender'))))";
        $placeholder = "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(droid_request, '$.user_info.user_name')), '') = 'akilimo-mcp'";

        $byGender = $this->userRequests($days)
            ->selectRaw("CASE
                    WHEN {$placeholder} THEN 'not_recorded'
                    WHEN {$gender} IN ('female', 'f', 'mwanamke') THEN 'female'
                    WHEN {$gender} IN ('male', 'm', 'mwanaume') THEN 'male'
                    ELSE 'not_recorded'
                END as gender_group, COUNT(*) as count")
            ->groupBy('gender_group')
            ->pluck('count', 'gender_group');

        return [
            'dailyRequests' => $dailyRequests,
            'byUseCase' => $byUseCase,
            'byCountry' => $byCountry,
            'byGender' => [
                'female' => (int) ($byGender['female'] ?? 0),
                'male' => (int) ($byGender['male'] ?? 0),
                'not_recorded' => (int) ($byGender['not_recorded'] ?? 0),
            ],
        ];
    }

    protected function getMetrics(int $days = 7): array
    {
        // Single query with conditional aggregation — hits the composite
        // (created_at, excluded) index added by the optimisation migration.
        $row = DB::table('api_requests')
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN excluded THEN 1 ELSE 0 END) as excluded_count')
            ->where('created_at', '>=', now()->subDays($days))
            ->first();

        $total = (int) ($row->total ?? 0);
        $excluded = (int) ($row->excluded_count ?? 0);

        // Success: real-user requests the compute service answered without an error.
        // Requests with nothing saved (the process stopped first) are counted apart.
        $outcome = $this->userRequests($days)
            ->selectRaw('SUM(CASE WHEN '.self::ANSWERED_SQL.' THEN 1 ELSE 0 END) as answered,
                SUM(CASE WHEN '.self::ANSWERED_SQL.' AND '.self::FAILED_SQL.' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN '.self::ANSWERED_SQL.' THEN 0 ELSE 1 END) as unanswered')
            ->first();

        $answered = (int) ($outcome->answered ?? 0);
        $failed = (int) ($outcome->failed ?? 0);

        // Only recent requests record a duration; failures (timeouts) are left out
        // so the figure describes the answers farmers actually got.
        $durations = $this->userRequests($days)
            ->whereNotNull('request_duration_ms')
            ->whereRaw('NOT ('.self::FAILED_SQL.')')
            ->orderBy('request_duration_ms')
            ->pluck('request_duration_ms')
            ->map(fn ($ms) => (int) $ms)
            ->all();

        return [
            'medianResponseMs' => $this->percentile($durations, 50),
            'p95ResponseMs' => $this->percentile($durations, 95),
            'timedRequests' => count($durations),
            'successRate' => $answered > 0 ? round((($answered - $failed) / $answered) * 100, 1) : null,
            'answeredRequests' => $answered,
            'failedRequests' => $failed,
            'unansweredRequests' => (int) ($outcome->unanswered ?? 0),
            'requestsLastDays' => $total - $excluded,
            'staffRequests' => $excluded,
            'uniqueDevices' => (int) $this->userRequests($days)
                ->whereNotNull('device_token')
                ->distinct()
                ->count('device_token'),
            'periodDays' => $days,
        ];
    }

    /**
     * Nearest-rank percentile of an ascending list; null when it is empty.
     *
     * @param  int[]  $sorted
     */
    protected function percentile(array $sorted, int $p): ?int
    {
        if ($sorted === []) {
            return null;
        }

        $rank = (int) ceil($p / 100 * count($sorted));

        return $sorted[max(0, $rank - 1)];
    }

    /**
     * Ratings from the app's feedback form. Satisfaction is 1–5; older rows
     * stored 0 for "no answer", which is left out of the average. The second
     * score changed from a 0–5 to a 0–10 (NPS) scale, so it is not reported.
     */
    protected function getFeedbackStats(int $days): array
    {
        $rated = DB::table('user_feedback')->whereBetween('akilimo_rec_rating', [1, 5]);

        $distribution = (clone $rated)
            ->selectRaw('akilimo_rec_rating as rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        return [
            'responses' => (int) DB::table('user_feedback')->count(),
            'responsesLastDays' => (int) DB::table('user_feedback')->where('created_at', '>=', now()->subDays($days))->count(),
            'avgSatisfaction' => ($avg = (clone $rated)->avg('akilimo_rec_rating')) !== null ? round((float) $avg, 2) : null,
            'ratedResponses' => (int) (clone $rated)->count(),
            'distribution' => collect(range(1, 5))->mapWithKeys(fn ($r) => [$r => (int) ($distribution[$r] ?? 0)])->all(),
            'lastResponseAt' => ($last = DB::table('user_feedback')->max('created_at')) ? Carbon::parse($last)->toIso8601String() : null,
        ];
    }

    /**
     * The latest failed computes with the error the service gave (admins only).
     */
    protected function getRecentFailures(int $limit = 5): array
    {
        return DB::table('api_requests')
            ->whereRaw(self::FAILED_SQL)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'country_code', 'use_case', 'created_at', 'plumber_response'])
            ->map(function ($r) {
                $body = json_decode((string) $r->plumber_response, true) ?: [];
                $message = trim(($body['error'] ?? 'Error').(isset($body['message']) ? ': '.$body['message'] : ''));

                return [
                    'id' => $r->id,
                    'country_code' => $r->country_code,
                    'use_case' => $r->use_case,
                    'status_code' => $body['status_code'] ?? null,
                    'message' => Str::limit($message, 160),
                    'created_at' => Carbon::parse($r->created_at)->toIso8601String(),
                ];
            })
            ->all();
    }

    /**
     * When each price list and coverage area was last changed (admins only).
     * Old prices make for bad advice, and nothing else warns about them.
     */
    protected function getDataFreshness(): array
    {
        $lists = [
            ['label' => 'Fertilizer prices', 'table' => 'fertilizer_prices', 'href' => '/admin/fertilizer-prices'],
            ['label' => 'Cassava prices', 'table' => 'cassava_prices', 'href' => '/admin/cassava-prices'],
            ['label' => 'Maize prices', 'table' => 'maize_prices', 'href' => '/admin/maize-prices'],
            ['label' => 'Potato prices', 'table' => 'potato_prices', 'href' => '/admin/potato-prices'],
            ['label' => 'Starch prices', 'table' => 'starch_prices', 'href' => '/admin/starch-prices'],
            ['label' => 'Default prices', 'table' => 'default_prices', 'href' => '/admin/default-prices'],
            ['label' => 'Operation costs', 'table' => 'operation_costs', 'href' => '/admin/operation-costs'],
        ];

        $prices = collect($lists)
            ->filter(fn ($l) => Schema::hasTable($l['table']))
            ->map(function ($l) {
                $row = DB::table($l['table'])
                    ->selectRaw('COUNT(*) as n, MAX(updated_at) as updated, MAX(created_at) as created')
                    ->first();
                $last = $row->updated ?? $row->created;

                return [
                    'label' => $l['label'],
                    'href' => $l['href'],
                    'rows' => (int) $row->n,
                    'last_updated' => $last ? Carbon::parse($last)->toIso8601String() : null,
                    // No date at all counts as stale: nobody can tell how old it is.
                    'stale' => ! $last || Carbon::parse($last)->lt(now()->subDays(self::STALE_AFTER_DAYS)),
                ];
            })
            ->values()
            ->all();

        $areas = DB::table('coverage_areas')->pluck('fetched_at', 'country_code');
        $coverage = Country::where('active', true)
            ->orderBy('code')
            ->pluck('code')
            ->map(fn ($code) => [
                'country_code' => $code,
                'fetched_at' => ($at = $areas[$code] ?? null) ? Carbon::parse($at)->toIso8601String() : null,
            ])
            ->all();

        return [
            'staleAfterDays' => self::STALE_AFTER_DAYS,
            'prices' => $prices,
            'coverage' => $coverage,
        ];
    }

    protected function getRecentRequests(): array
    {
        return $this->model
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'request_id' => $r->request_id,
                'device_token' => $r->device_token ? substr($r->device_token, 0, 8).'****' : null,
                'duration_ms' => $r->request_duration_ms,
                'created_at' => $r->created_at?->toIso8601String(),
                'use_case' => $r->use_case,
                'country_code' => $r->country_code,
            ])
            ->all();
    }
}
