<?php

namespace App\Repositories;

use App\Models\Crop;

class CropRepo extends BaseRepo
{
    protected function model(): string
    {
        return Crop::class;
    }
}
