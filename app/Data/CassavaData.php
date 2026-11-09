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
class CassavaData extends Data
{
    public string $produceType;

    public float $unitWeight;

    public float $unitPrice;

    #[MapInputName('unit_price_maize1')]
    public float $unitPriceMaize1 = 0.0;

    #[MapInputName('unit_price_maize2')]
    public float $unitPriceMaize2 = 0.0;

    #[MapInputName('unit_price_potato1')]
    public float $unitPricePotato1 = 0.0;

    #[MapInputName('unit_price_potato2')]
    public float $unitPricePotato2 = 0.0;
}
