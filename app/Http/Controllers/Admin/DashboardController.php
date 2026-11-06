<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnumUserRole as UserRole;
use App\Repositories\ApiRequestStatsRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(protected ApiRequestStatsRepo $statsRepo) {}

    public function index(Request $request): Response
    {
        $chartDays = (int) $request->input('chart_days', 30);
        $metricsDays = (int) $request->input('metrics_days', 7);

        $stats = $request->input('refresh')
            ? $this->statsRepo->refreshDashboardStats($chartDays, $metricsDays)
            : $this->statsRepo->getDashboardStats($chartDays, $metricsDays);

        // The cache is shared, so partners' copy is trimmed here rather than in the repo.
        if ($request->user()?->role !== UserRole::Admin) {
            $stats = Arr::except($stats, ApiRequestStatsRepo::ADMIN_ONLY_KEYS);
        }

        return Inertia::render('Dashboard', $stats);
    }
}
