<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fill default_prices.fertilizer_key for the existing per-bag rows. Each item is
 * matched to its fertiliser first by label (urea = UREA, DAP = DAP), then by
 * comparing letters and digits only, with the country suffix removed
 * (NPK201010 = NPK_20_10_10_GH, FOMIIMBURA ~ FOM_II_MBUR_BI: either may be the
 * prefix of the other). Only a single match is accepted; an item with none keeps
 * a null key and has no option list until a reviewer sets one.
 */
return new class extends Migration {
    public function up(): void
    {
        $fertilizers = DB::table('fertilizers')->get(['country', 'fertilizer_label', 'fertilizer_key'])->groupBy('country');

        foreach (DB::table('default_prices')->where('unit', 'per_bag')->whereNull('fertilizer_key')->get(['id', 'country', 'item']) as $price) {
            $candidates = $fertilizers->get($price->country, collect());
            $item = $this->normalise($price->item);

            $matches = $candidates->filter(fn ($f) => strcasecmp((string) $f->fertilizer_label, $price->item) === 0);

            if ($matches->isEmpty()) {
                $matches = $candidates->filter(function ($f) use ($item, $price) {
                    $key = $this->normalise(preg_replace('/_' . preg_quote($price->country, '/') . '$/', '', (string) $f->fertilizer_key));

                    return $key !== '' && $item !== '' && (str_starts_with($item, $key) || str_starts_with($key, $item));
                });
            }

            if ($matches->count() === 1) {
                DB::table('default_prices')->where('id', $price->id)->update(['fertilizer_key' => $matches->first()->fertilizer_key]);
            }
        }
    }

    public function down(): void
    {
        DB::table('default_prices')->update(['fertilizer_key' => null]);
    }

    private function normalise(string $value): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $value));
    }
};
