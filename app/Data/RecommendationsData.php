<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class RecommendationsData extends Data
{
    public bool $fertilizerRec;

    public bool $plantingPracticesRec;

    public bool $scheduledPlantingRec;

    public bool $scheduledHarvestRec;

    public string $lang = 'en';
}
