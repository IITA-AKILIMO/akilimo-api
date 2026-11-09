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
class TractorCostsData extends Data
{
    public bool $tractorPlough;

    public bool $tractorHarrow;

    public bool $tractorRidger;

    public string $costLmoAreaBasis;

    public float $costTractorPloughing;

    public float $costTractorHarrowing;

    public float $costTractorRidging;
}
