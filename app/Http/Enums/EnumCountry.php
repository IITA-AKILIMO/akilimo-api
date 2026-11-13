<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Enums;

use LaracraftTech\LaravelUsefulAdditions\Traits\UsefulEnums;

enum EnumCountry: string
{
    use UsefulEnums;

    case KENYA = 'KE';
    case TANZANIA = 'TZ';
    case NIGERIA = 'NG';
    case RWANDA = 'RW';
    case GHANA = 'GH';
    case BURUNDI = 'BI';

    case ALL = 'ALL';

    public function currency(): string
    {
        return match ($this) {
            self::KENYA => 'KES',
            self::TANZANIA => 'TZS',
            self::NIGERIA => 'NGN',
            self::RWANDA => 'RWF',
            self::GHANA => 'GHS',
            self::BURUNDI => 'BIF',
            self::ALL => 'USD',
        };
    }

    public static function fromCode(string $code): self
    {
        return match (strtoupper($code)) {
            self::KENYA->value => self::KENYA,
            self::TANZANIA->value => self::TANZANIA,
            self::NIGERIA->value => self::NIGERIA,
            self::RWANDA->value => self::RWANDA,
            self::GHANA->value => self::GHANA,
            self::BURUNDI->value => self::BURUNDI,
            default => self::ALL
        };

    }
}
