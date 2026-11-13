<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Data;

use App\Data\Transformers\LowercaseTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class FarmInformationData extends Data
{
    public string $countryCode;

    public string $useCase;

    public float $mapLat;

    public float $mapLong;

    public float $fieldSize;

    #[WithTransformer(LowercaseTransformer::class)]
    public string $areaUnit;
}
