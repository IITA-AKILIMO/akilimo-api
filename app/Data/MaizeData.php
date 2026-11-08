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
class MaizeData extends Data
{
    public string $produceType;

    public float $unitWeight;

    public float $unitPrice;

    #[MapInputName('unit_price_maize_1')]
    public int $unitPriceMaize1 = 0;

    #[MapInputName('unit_price_maize_2')]
    public int $unitPriceMaize2 = 0;

    #[MapInputName('unit_price_potato_1')]
    public int $unitPricePotato1 = 0;

    #[MapInputName('unit_price_potato_2')]
    public int $unitPricePotato2 = 0;
}
