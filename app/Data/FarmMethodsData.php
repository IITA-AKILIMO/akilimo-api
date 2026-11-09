<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Data;

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class FarmMethodsData extends Data
{
    public ?string $methodPloughing;

    public ?string $methodHarrowing;

    public ?string $methodRidging;

    public ?string $methodWeeding;
}
