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
class FertilizerData extends Data
{
    public string $name;

    #[MapInputName('fertilizer_label')]
    public string $label;

    #[MapInputName('fertilizer_type')]
    public string $type;

    #[MapInputName('key')]
    public string $key;

    public float $weight;

    #[MapInputName('price')]
    public float $pricePerBag = 0.0;

    #[MapInputName('selected')]
    public bool $selected = false;
}
