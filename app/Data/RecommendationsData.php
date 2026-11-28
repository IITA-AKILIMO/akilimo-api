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
    public bool $fertilizerRec = false;

    public bool $plantingPracticesRec = false;

    public bool $scheduledPlantingRec = false;

    public bool $scheduledHarvestRec = false;

    /** Planting date and cultivar advice (PDC): the compute service has it for NG maize only */
    public bool $plantingDateCultivarRec = false;

    /** The crop PDC advice is for; the compute service defaults to (and only supports) maize */
    public string $crop = 'maize';

    public string $lang = 'en';
}
