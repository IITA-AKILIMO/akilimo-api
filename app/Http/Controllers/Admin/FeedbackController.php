<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Repositories\UserFeedBackRepo;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function __construct(protected UserFeedBackRepo $repo) {}

    public function index(Request $request): Response
    {
        $perPage = min((int)$request->get('per_page', 25), 100);
        $orderBy = $request->get('sort_by', 'created_at');
        $direction = $request->get('sort_dir', 'desc');

        $filters = [
            'use_case' => (string)$request->get('use_case', ''),
            'user_type' => (string)$request->get('user_type', ''),
            'language' => (string)$request->get('language', ''),
            'date_from' => (string)$request->get('date_from', ''),
            'date_to' => (string)$request->get('date_to', ''),
            'search' => (string)$request->get('search', ''),
        ];

        $paginator = $this->repo->paginate($perPage, $orderBy, $direction, $filters);

        return Inertia::render('Feedback/Index', [
            'feedback' => [
                'data' => collect($paginator->items())->map(fn ($f) => [
                    'id' => $f->id,
                    'device_token' => $f->device_token ? substr($f->device_token, 0, 8) . '****' : null,
                    'use_case' => $f->use_case,
                    'user_type' => $f->user_type,
                    'akilimo_rec_rating' => $f->akilimo_rec_rating,
                    'akilimo_useful_rating' => $f->akilimo_useful_rating,
                    'language' => $f->language,
                    'created_at' => $f->created_at?->toIso8601String(),
                ])->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
            'filters' => array_merge($filters, ['sort_by' => $orderBy, 'sort_dir' => $direction]),
            'use_cases' => $this->repo->useCases(),
            'languages' => $this->repo->languages(),
        ]);
    }
}
