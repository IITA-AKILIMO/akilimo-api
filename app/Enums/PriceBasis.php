<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Enums;

/**
 * Which reference fertiliser price a country uses when a farmer has not said
 * whether they get subsidised fertiliser (decision D1).
 */
enum PriceBasis: string
{
    case OpenMarket = 'open_market';
    case Subsidised = 'subsidised';

    public function label(): string
    {
        return match ($this) {
            self::OpenMarket => 'Open market',
            self::Subsidised => 'Subsidised',
        };
    }

    /** @return string[] */
    public static function values(): array
    {
        return array_map(fn (self $b) => $b->value, self::cases());
    }
}
