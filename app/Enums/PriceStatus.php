<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Enums;

/**
 * Lifecycle of a catalogue row. Only `active` rows are served to clients.
 */
enum PriceStatus: string
{
    case Draft = 'draft';
    case PendingSecondReview = 'pending_second_review';
    case Active = 'active';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingSecondReview => 'Pending second review',
            self::Active => 'Active',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /** @return string[] */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
