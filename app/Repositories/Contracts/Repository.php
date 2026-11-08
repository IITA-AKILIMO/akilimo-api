<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface Repository
{
    /** @return Collection<int, TModel> */
    public function all(): Collection;

    /** @return TModel|null */
    public function find(int|string $id): ?Model;

    /** @return TModel|null */
    public function selectOne(array $conditions): ?Model;

    /** @return TModel */
    public function create(array $data): Model;

    /** @return TModel|null */
    public function update(int|string $id, array $data): ?Model;

    public function delete(int|string $id): bool;
}
