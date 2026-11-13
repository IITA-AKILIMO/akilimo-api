<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class BaseModel extends EloquentModel
{
    protected array $commonCasts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getCasts(): array
    {
        return array_merge($this->casts, $this->commonCasts);
    }
}
