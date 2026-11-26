<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AppVersionFormRequest;
use App\Service\AppVersionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Where an admin sets which app versions are current and which are no longer allowed to run.
 * The same rules the API's admin endpoint uses (AppVersionService).
 */
class AppVersionController extends AdminController
{
    public function __construct(protected AppVersionService $service) {}

    public function index(): Response
    {
        return Inertia::render('AppVersions/Index', [
            'versions' => collect($this->service->all())
                ->map(fn (array $v, string $platform) => ['platform' => $platform, ...$v])
                ->values()
                ->all(),
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function update(AppVersionFormRequest $request, string $platform): RedirectResponse
    {
        abort_unless(in_array($platform, AppVersionService::PLATFORMS, true), 404);

        $data = $request->validated();
        // The form always sends every field, so a blank one means "clear it" (the service keeps a null as unchanged)
        $data['message'] = $data['message'] ?? '';
        $data['store_url'] = $data['store_url'] ?? '';

        $this->service->update($platform, $data);

        return redirect()->route('admin.app-versions.index')
            ->with('success', ucfirst($platform) . ' versions saved.');
    }
}
