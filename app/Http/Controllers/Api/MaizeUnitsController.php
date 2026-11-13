<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaizeUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaizeUnitsController extends Controller
{
    /**
     * List Maize Units
     *
     * The units maize grain is sold in, for a client to offer as the unit of sale. The
     * label is the name of the unit of sale (ONE_KG, FIFTY_KG, HUNDRED_KG, THOUSAND_KG)
     * and `unit_weight` is in kilograms. Maize prices are per tonne: a client shows the
     * price for the chosen unit as average x unit_weight / 1000. The default is the
     * standard 100 kg bag.
     *
     * @unauthenticated
     */
    public function index(Request $request): JsonResponse
    {
        $units = MaizeUnit::query()->where('is_active', true)->orderBy('sort_order')->orderBy('unit_weight')->get();

        return response()->json([
            'data' => $units->map(fn (MaizeUnit $u) => [
                /** Unique identifier for the unit */
                'id' => $u->id,
                /** Unit label/code */
                'label' => $u->label,
                /** Unit weight in kilograms */
                'unit_weight' => $u->unit_weight,
                /** Sort order for display */
                'sort_order' => $u->sort_order,
                'description' => $u->description,
                /** Whether the client should preselect this unit */
                'default' => $u->is_default,
                /** Whether the unit is active */
                'active' => $u->is_active,
            ])->values(),
        ]);
    }
}
