<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);
        $this->call(CountriesSeeder::class);
        $this->call(CountryCurrenciesSeeder::class);
        // The lists the app and the price catalogue refer to, before the prices
        $this->call(CropsSeeder::class);
        $this->call(CassavaUnitsSeeder::class);
        $this->call(FertilizerSeeder::class);
        $this->call(TrafficExclusionSeeder::class);
        $this->call(StarchPricesSeeder::class);
        $this->call(TranslationsSeeder::class);
        $this->call(PriceCatalogueSeeder::class);
    }
}
