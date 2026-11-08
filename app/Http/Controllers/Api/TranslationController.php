<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TranslationRequest;
use App\Http\Resources\Collections\TranslationResourceCollection;
use App\Http\Resources\TranslationResource;
use App\Repositories\TranslationRepo;
use App\Traits\HasPaginationParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    use HasPaginationParams;

    public function __construct(protected TranslationRepo $translationRepo) {}

    public function index(Request $request): TranslationResourceCollection
    {
        $perPage = $this->getPerPage($request);
        $orderBy = $this->getOrderBy($request, ['created_at'], 'created_at');
        $sort = $this->getSortDirection($request);

        $items = $this->translationRepo->paginateWithSort(
            perPage: $perPage,
            orderBy: $orderBy,
            direction: $sort,
        );

        return TranslationResourceCollection::make($items);
    }

    public function store(TranslationRequest $request): JsonResponse
    {
        $translation = $this->translationRepo->create($request->validated());

        /**
         * @status 201
         */
        return response()->json([
            'data' => new TranslationResource($translation),
            'message' => 'Translation created.',
        ], 201);
    }

    public function update(TranslationRequest $request, int $id): JsonResponse
    {
        $translation = $this->translationRepo->update($id, $request->validated());

        return response()->json([
            'data' => new TranslationResource($translation),
            'message' => 'Translation updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->translationRepo->delete($id);

        return response()->json(null, 204);
    }
}
