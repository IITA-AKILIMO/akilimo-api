<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Support;

/**
 * The words a price choice is shown with. The app (and any other client) shows a
 * label for each choice, then maps the farmer's pick back to its value and sends
 * that to the API. Ordinary choices are labelled with their price; the two rows
 * every list ends with are labelled in words, not as a price:
 *
 *  - 0 is "I don't know": the engine then uses the catalogue price.
 *  - below 0 is "Exact price": the farmer types their own.
 */
final class PriceMarker
{
    public const UNKNOWN = "I don't know";

    public const EXACT = 'Exact price';

    /** The investment list uses its 0 row for "type your own amount". */
    public const EXACT_AMOUNT = 'Exact amount';

    /**
     * The words for a price or the average of a range: 0 is unknown, below 0 is
     * exact, anything else has none.
     */
    public static function describe(float|int|string|null $price): ?string
    {
        $value = (float) $price;

        return match (true) {
            $value < 0.0 => self::EXACT,
            $value === 0.0 => self::UNKNOWN,
            default => null,
        };
    }

    /**
     * The label to show for a price: the words for a marker row, otherwise the
     * price with its currency symbol (54,000 TSh).
     */
    public static function labelForPrice(float|int|string|null $price, ?string $symbol = null): string
    {
        return self::describe($price) ?? trim(self::amount($price) . ' ' . $symbol);
    }

    /**
     * The label to show for a range: the words for the exact-price row, otherwise
     * "7,200 – 9,600 ₦", or a single value when the two ends are the same.
     */
    public static function labelForRange(float|int|string|null $min, float|int|string|null $max, ?string $symbol = null): string
    {
        $words = self::describe(((float) $min + (float) $max) / 2);

        if ($words !== null) {
            return $words;
        }

        $range = (float) $min === (float) $max ? self::amount($min) : self::amount($min) . ' – ' . self::amount($max);

        return trim($range . ' ' . $symbol);
    }

    /**
     * 12000.00 as 12,000 and 8.5 as 8.5.
     */
    public static function amount(float|int|string|null $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ','), '0'), '.');
    }
}
