<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DefaultPriceResource;
use App\Models\Country;
use App\Repositories\DefaultPriceRepo;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PriceController extends Controller
{
    public function __construct(protected DefaultPriceRepo $repo) {}

    /**
     * List catalogue prices
     *
     * Returns the reviewed, active prices a country uses. The response is
     * ETag-enabled: send `If-None-Match` back on the next request to get a 304
     * while nothing has changed. `updated_since` returns only rows changed
     * after that time, for incremental syncs.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'country', description: 'ISO 3166-1 alpha-2 country code.', type: 'string', required: true)]
    #[QueryParameter(name: 'updated_since', description: 'Only return rows updated after this ISO 8601 time.', type: 'string')]
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'updated_since' => ['nullable', 'date'],
        ]);

        // Burundi was filed as "BU" before its ISO code; older clients may still ask for it.
        $code = strtoupper($validated['country']);
        $code = $code === 'BU' ? 'BI' : $code;

        $updatedSince = isset($validated['updated_since'])
            ? Carbon::parse($validated['updated_since'])
            : null;

        $rows = $this->repo->activeForCountry($code, $updatedSince);
        $country = Country::query()->where('code', $code)->first();

        $data = [
            'country' => $code,
            'default_price_basis' => $country?->default_price_basis?->value
                ?? config('price_catalogue.default_basis'),
            'data' => DefaultPriceResource::collection($rows)->resolve($request),
            'updated_at' => $rows->max('updated_at')?->toIso8601String(),
        ];

        $response = response()->json($data);
        $response->setEtag(md5((string)json_encode($data)));

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $response;
    }
}
