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

    /** The most ids GET /history/ids returns. */
    private const MAX_IDS = 5000;

    /** The most items one bulk delete takes. */
    private const MAX_BULK_DELETE = 500;

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
     * The ids of all saved advice
     *
     * Newest first, a few dozen bytes each, at most 5000 (`meta.truncated` says when the list was cut). An app compares it with
     * its own copies and drops the ones that were deleted elsewhere, without downloading the whole history again.
     */
    public function ids(Request $request): JsonResponse
    {
        $result = $this->requests->requestIdsForUser($this->user($request)->id, self::MAX_IDS);

        return response()->json([
            'data' => $result['ids'],
            'meta' => ['count' => count($result['ids']), 'truncated' => $result['truncated']],
        ]);
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

    /**
     * Remove one saved item
     *
     * A soft delete: the item disappears from the farmer's account, but the request is kept for statistics with the link to the
     * person removed (`user_id` set to null and the phone's `device_token` cleared), as when an account is deleted.
     */
    public function destroy(Request $request, string $requestId): JsonResponse
    {
        $released = $this->requests->releaseForUser($this->user($request)->id, $requestId);
        abort_if(!$released, 404, 'Saved advice not found.');

        return response()->json(['message' => 'Saved advice deleted.']);
    }

    /**
     * Remove several saved items at once
     *
     * Body `{"ids": ["<request_id>", ...]}`, up to 500. The same soft delete as for one item. Ids that are not the caller's or no
     * longer exist are skipped, so a repeated request is harmless; `deleted` is how many items left the account.
     */
    public function destroyMany(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:' . self::MAX_BULK_DELETE],
            'ids.*' => ['required', 'uuid'],
        ]);

        $deleted = $this->requests->releaseManyForUser($this->user($request)->id, array_values(array_unique($data['ids'])));

        return response()->json(['message' => 'Saved advice deleted.', 'deleted' => $deleted]);
    }

    /**
     * Remove all saved advice
     *
     * The same soft delete for every saved recommendation of the signed-in farmer; the account stays. The requests are kept for
     * statistics with the link to the person removed. `deleted` is how many items were removed from the account.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $deleted = $this->requests->releaseAllForUser($this->user($request)->id);

        return response()->json(['message' => 'All saved advice deleted.', 'deleted' => $deleted]);
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
