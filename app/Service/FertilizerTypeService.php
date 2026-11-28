<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Models\FertilizerType;
use App\Repositories\FertilizerTypeRepo;
use App\Repositories\PriceItemRepo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds a new fertiliser. The type code (the primary key every fertiliser key is
 * built from) is generated from the name so nobody types it: NPK 20:10:10 becomes
 * NPK_20_10_10, and a code already taken gets a number (UREA_2).
 *
 * The fertiliser is also made available to price: its catalogue label becomes a
 * row in price_items (per bag), so a price can be added for it in the catalogue.
 */
class FertilizerTypeService
{
    public function __construct(
        private readonly FertilizerTypeRepo $types,
        private readonly PriceItemRepo $items,
    ) {}

    /**
     * The code for a name: upper case letters and digits, joined by underscores.
     */
    public function codeFor(string $name): string
    {
        return trim((string)preg_replace('/[^A-Z0-9]+/', '_', strtoupper(Str::ascii($name))), '_');
    }

    /**
     * The catalogue label for a name: its letters and digits, as the existing
     * ones are (NPK 20:10:10 is NPK201010).
     */
    public function labelFor(string $name): string
    {
        return (string)preg_replace('/[^A-Za-z0-9]+/', '', Str::ascii($name));
    }

    /**
     * A code that is not taken yet, adding _2, _3 ... when it is. The database
     * compares codes without regard to case, so the check does too.
     */
    public function uniqueCode(string $code): string
    {
        $candidate = $code;
        $n = 1;

        while ($this->types->existsWithConditions(['type' => $candidate])) {
            $candidate = $code . '_' . (++$n);
        }

        return $candidate;
    }

    /**
     * @param array{name: string, weight?: int|null, label?: string|null, category?: string|null} $data
     *
     * @throws \Throwable
     */
    public function create(array $data): FertilizerType
    {
        return DB::transaction(function () use ($data) {
            $name = trim($data['name']);
            $label = trim((string)($data['label'] ?? '')) ?: $this->labelFor($name);

            $type = $this->types->create([
                'type' => $this->uniqueCode($this->codeFor($name)),
                'label' => $label,
                'name' => $name,
                'weight' => $data['weight'] ?? 50,
                'category' => $data['category'] ?? 'OTHER',
            ]);

            // A catalogue price needs its item in price_items
            $this->items->ensure($label, 'per_bag', 'FERTILIZER');

            return $type;
        });
    }
}
