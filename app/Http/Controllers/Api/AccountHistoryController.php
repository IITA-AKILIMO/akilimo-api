<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Resources\FarmerHistoryResource;
use App\Models\User;
use App\Repositories\ApiRequestRepo;
use App\Service\FarmerAccountService;
use App\Traits\HasPaginationParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

/**
 * A farmer's own saved advice, and deleting the account. Every route needs a farmer token with the `history:own`
 * ability and a recorded consent, and only ever sees rows linked to the signed-in account.
 *
 * @tags Farmer accounts
 */
class AccountHistoryController extends Controller
{
    use HasPaginationParams;

    public function __construct(
        private readonly ApiRequestRepo $requests,
        private readonly FarmerAccountService $accounts,
    ) {}

    /**
     * Saved advice
     *
     * The signed-in farmer's saved recommendations, newest first, paginated (`per_page`, up to 100).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $page = $this->requests->paginateForUser($this->user($request)->id, $this->getPerPage($request, 20));

        return FarmerHistoryResource::collection($page);
    }

    /**
     * One saved recommendation with its result
     *
     * 404 when the id does not exist or belongs to someone else.
     */
    public function show(Request $request, string $requestId): FarmerHistoryResource
    {
        $row = $this->requests->findForUser($this->user($request)->id, $requestId);
        abort_if($row === null, 404, 'Saved advice not found.');

        return new FarmerHistoryResource($row, detail: true);
    }

    /** Remove one saved item. */
    public function destroy(Request $request, string $requestId): JsonResponse
    {
        $row = $this->requests->findForUser($this->user($request)->id, $requestId);
        abort_if($row === null, 404, 'Saved advice not found.');

        $row->delete();

        return response()->json(['message' => 'Saved advice deleted.']);
    }

    /**
     * Delete the account
     *
     * Removes the account, its tokens and keys. The farmer's past requests are kept as anonymous statistics: the link to
     * the account is removed and the phone's device_token is cleared on those rows.
     */
    public function destroyAccount(Request $request): JsonResponse
    {
        $this->accounts->deleteAccount($this->user($request));

        return response()->json(['message' => 'Account deleted.']);
    }

    private function user(Request $request): User
    {
        return $request->user();
    }
}
