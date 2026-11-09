<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepo<Translation>
 */
class TranslationRepo extends BaseRepo
{
    protected function model(): string
    {
        return Translation::class;
    }

    public function paginateWithSearch(
        string $search,
        int $perPage = 20,
        string $orderBy = 'key',
        string $direction = 'asc',
    ): LengthAwarePaginator {
        return $this->query()
            ->where('key', 'like', '%' . $search . '%')
            ->orderBy($orderBy, $direction)
            ->paginate($perPage);
    }

    /**
     * Load up to $limit translations for batch editing, optionally filtered by key search.
     *
     * @return Collection<int, Translation>
     */
    public function forBatchEdit(string $search = '', int $limit = 50): Collection
    {
        $query = $this->query()->orderBy('key');

        if ($search !== '') {
            $query->where('key', 'like', '%' . $search . '%');
        }

        return $query->limit($limit)->get();
    }

    public function deleteByIds(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $this->query()->whereIn('id', $ids)->delete();
    }
}
