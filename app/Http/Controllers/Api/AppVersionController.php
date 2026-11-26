<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppVersionUpdateRequest;
use App\Http\Requests\AppVersionRequest;
use App\Service\AppVersionService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function __construct(protected AppVersionService $versions) {}

    /**
     * App version gate
     *
     * The oldest and the newest version code of the mobile app, per platform. A phone running a version code below
     * `min_supported` must update before it can be used; one below `latest` is only offered the update. `message` and
     * `store_url` are optional. The values come from configuration, so the minimum is raised without a deploy of the app.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'platform', description: 'Only this platform (android). Default: all.', type: 'string')]
    public function index(Request $request): JsonResponse
    {
        $query = $request->validate((new AppVersionRequest)->rules());

        return response()->json(
            ['data' => $this->versions->all($query['platform'] ?? null)],
            200,
            ['Cache-Control' => 'public, max-age=' . $this->versions->cacheSeconds()],
        );
    }

    /**
     * Set the app version gate (admin)
     *
     * Stores `min_supported`, `latest`, `message` or `store_url` for a platform (android by default); fields left out keep
     * their value. The Android publish workflow sends only `latest` after an upload; raise `min_supported` to force an update.
     * A `latest` below the minimum is raised to it.
     * @throws \Throwable
     */
    public function update(AppVersionUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $platform = $data['platform'] ?? 'android';

        return response()->json([
            'data' => [$platform => $this->versions->update($platform, $data)],
            'message' => 'App version updated.',
        ]);
    }
}
