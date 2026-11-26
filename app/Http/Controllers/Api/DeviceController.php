<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\DeviceRequest;
use App\Service\PushDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class DeviceController extends Controller
{
    public function __construct(protected PushDeviceService $devices) {}

    /**
     * Register a phone for push
     *
     * Registers or refreshes the phone's Firebase token under its `device_token` (the install id the app sends with
     * account and compute requests). Call it at start, when Firebase gives a new token, and when country, language,
     * account or the push switch change. A valid farmer token (optional) links the phone to the account, so messages
     * addressed to the farmer reach it; without one the link is cleared. Open to everyone.
     *
     * @unauthenticated
     */
    public function upsert(DeviceRequest $request): JsonResponse
    {
        $device = $this->devices->register($request->validated(), $request->attributes->get('farmer_id'));

        return response()->json([
            'data' => [
                'device_token' => $device->device_token,
                'platform' => $device->platform,
                'app_version' => $device->app_version,
                'country' => $device->country,
                'language' => $device->language,
                'linked_to_account' => $device->user_id !== null,
                'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            ],
            'message' => 'Device registered.',
        ]);
    }

    /**
     * Unregister a phone
     *
     * Forgets the phone's Firebase token (sign-out, push switched off). Answers 204 whether or not it was registered.
     *
     * @unauthenticated
     */
    public function destroy(Request $request): Response
    {
        $data = $request->validate(['device_token' => ['required', 'string', 'max:255']]);
        $this->devices->unregister($data['device_token']);

        return response()->noContent();
    }
}
