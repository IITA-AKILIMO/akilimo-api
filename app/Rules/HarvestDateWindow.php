<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Rules;

use App\Support\PlantingSchedule;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

/**
 * The harvest date must fall PlantingSchedule::MIN_DAYS to MAX_DAYS after the planting date.
 * Only checked when a scheduled planting or harvest recommendation is asked for, the
 * only case the engine enforces it; the field's own rules cover a missing or malformed date.
 */
class HarvestDateWindow implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function __construct(
        private readonly string $plantingKey,
        private readonly ?string $recommendationsKey = null,
    ) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->recommendationsKey !== null && !$this->scheduleRequested()) {
            return;
        }

        try {
            $planting = Carbon::parse((string)data_get($this->data, $this->plantingKey));
            $harvest = Carbon::parse((string)$value);
        } catch (Throwable) {
            return; // not a date: the 'date' rule reports it
        }

        if (!PlantingSchedule::isValid($planting, $harvest)) {
            $fail(PlantingSchedule::message());
        }
    }

    private function scheduleRequested(): bool
    {
        return filter_var(data_get($this->data, $this->recommendationsKey . '.scheduled_planting_rec'), FILTER_VALIDATE_BOOLEAN)
            || filter_var(data_get($this->data, $this->recommendationsKey . '.scheduled_harvest_rec'), FILTER_VALIDATE_BOOLEAN);
    }
}
