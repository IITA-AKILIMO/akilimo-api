<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeedBackRequest;
use App\Http\Resources\Collections\UserFeedbackResourceCollection;
use App\Http\Resources\UserFeedbackResource;
use App\Repositories\UserFeedBackRepo;
use App\Traits\HasPaginationParams;
use Illuminate\Http\Request;

class UserFeedbackController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected UserFeedBackRepo $repo) {}

    public function index(Request $request): UserFeedbackResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['created_at', 'updated_at', 'akilimo_usage'], 'created_at');
        $sort = $this->getSortDirection($request);

        $userFeedbackData = $this->repo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
        );

        return UserFeedbackResourceCollection::make($userFeedbackData);
    }

    public function store(FeedBackRequest $request): UserFeedbackResource
    {
        $data = $request->toPersistenceArray();
        $feedBack = $this->repo->create($data);

        /**
         * @status 201
         */
        return UserFeedbackResource::make($feedBack);
    }
}
