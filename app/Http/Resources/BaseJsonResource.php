<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseJsonResource extends JsonResource
{
    /**
     * Format a date/datetime value for API output.
     *
     * Override this method in a subclass to change the format for a specific resource.
     *
     * Example — return a date-only string:
     *   protected function formatDate($value):string
     *   {
     *       return $value?->toDateString();
     *   }
     */
    protected function formatDate(mixed $value): ?string
    {
        return $value?->toIso8601String();
    }
}
