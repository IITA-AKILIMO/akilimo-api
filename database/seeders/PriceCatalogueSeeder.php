<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The price catalogue (price_catalogue.csv): the reviewed prices, the drafts waiting for review (each with
 * its source and confidence), and the investment amounts. A row keeps its id, because the app's price option
 * lists are built from it (id x 10 + step).
 *
 * Adds what is missing and never changes a row that exists: a reviewed price that an admin has activated
 * or edited is not put back to the value in the file, and the review rules (draft, activate, second review)
 * stay in charge. Run after FertilizerSeeder, so a fertilizer's price is linked to it.
 */
class PriceCatalogueSeeder extends Seeder
{
    use ReadsSeedCsv;

    public function run(): void
    {
        foreach ($this->csvRows('price_catalogue.csv') as $row) {
            // A fertilizer is only linked when it exists (a price for one that is not sold stays unlinked)
            $type = $row['fertilizer_type'];
            if ($type !== null && ! DB::table('fertilizer_types')->where('type', $type)->exists()) {
                $type = null;
            }

            DB::table('price_catalogue')->insertOrIgnore([
                'id' => (int) $row['id'],
                'country' => $row['country'],
                'region' => $row['region'],
                'item' => $row['item'],
                'fertilizer_type' => $type,
                'price' => (float) $row['price'],
                'subsidised_price' => $row['subsidised_price'] === null ? null : (float) $row['subsidised_price'],
                'unit' => $row['unit'],
                'bag_kg' => $row['bag_kg'] === null ? null : (int) $row['bag_kg'],
                'min_price' => $row['min_price'] === null ? null : (float) $row['min_price'],
                'max_price' => $row['max_price'] === null ? null : (float) $row['max_price'],
                'status' => $row['status'] ?? 'draft',
                'source' => $row['source'],
                'source_date' => $row['source_date'],
                'confidence' => $row['confidence'],
                'effective_from' => $row['effective_from'],
                'currency' => $row['currency'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
