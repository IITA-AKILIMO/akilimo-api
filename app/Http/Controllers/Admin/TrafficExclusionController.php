<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TrafficExclusionRequest;
use App\Models\TrafficExclusion;
use App\Repositories\TrafficExclusionRepo;
use App\Service\TrafficExclusionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Rules that mark requests as staff or test traffic, left out of the dashboard and reports.
 * Every change recomputes the flag on all requests.
 */
class TrafficExclusionController extends AdminController
{
    public function __construct(
        protected TrafficExclusionRepo $repo,
        protected TrafficExclusionService $exclusions,
    ) {}

    public function index(): Response
    {
        $rules = TrafficExclusion::query()->orderByDesc('active')->orderBy('id')->get();
        $matches = $this->exclusions->matchCounts($rules);

        return Inertia::render('TrafficExclusions/Index', [
            'rules' => $rules
                ->map(fn (TrafficExclusion $r) => [
                    'id' => $r->id,
                    'field' => $r->field,
                    'match_type' => $r->match_type,
                    'value' => $r->value,
                    'note' => $r->note,
                    'active' => $r->active,
                    'matches' => $matches[$r->id] ?? 0,
                ]),
            'fields' => TrafficExclusionService::FIELDS,
            'matchTypes' => TrafficExclusionService::MATCH_TYPES,
            'totals' => [
                'requests' => (int) DB::table('api_requests')->count(),
                'excluded' => (int) DB::table('api_requests')->where('excluded', true)->count(),
            ],
        ]);
    }

    public function store(TrafficExclusionRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());

        return $this->refreshed('Rule added.');
    }

    public function update(TrafficExclusionRequest $request, int $id): RedirectResponse
    {
        $this->repo->update($id, $request->validated());

        return $this->refreshed('Rule updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repo->delete($id);

        return $this->refreshed('Rule deleted.');
    }

    private function refreshed(string $message): RedirectResponse
    {
        $changed = $this->exclusions->refresh();

        return redirect()->route('admin.traffic-exclusions.index')
            ->with('success', $message.' '.trans_choice('{0} No request changed.|{1} 1 request changed.|[2,*] :count requests changed.', $changed, ['count' => number_format($changed)]));
    }
}
