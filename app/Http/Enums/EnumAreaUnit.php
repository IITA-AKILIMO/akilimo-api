<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Enums;

use LaracraftTech\LaravelUsefulAdditions\Traits\UsefulEnums;

enum EnumAreaUnit: string
{
    use UsefulEnums;

    case Acre = 'acre';
    case Ha = 'ha';
    case M2 = 'm2';
    case Are = 'are';
    //    case Ekari = 'ekari';
    //    case Hekta = 'hekta';

    public static function normalize(string $value): self
    {
        $value = strtolower($value);

        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        return self::Acre;
    }
}
