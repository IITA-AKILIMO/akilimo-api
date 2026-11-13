<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every price item has a category (price_items.category, a foreign key to
 * price_categories), so Default prices can be filtered by it. The category is on
 * the item, not copied onto each price, so a country's price can never disagree
 * with it.
 *
 * Backfilled from the item: a fertilizer's catalogue label (fertilizer_types.label),
 * or a name starting NPK or FOMI, is FERTILIZER; cassUP, maize_* and sweetPotato_* are PRODUCE; manual_* and
 * weeding* are LABOUR; tractor_* is MACHINERY; investment_amount is INVESTMENT;
 * anything else is OTHER until someone classifies it in Price rules > Price items.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('price_items', function (Blueprint $table) {
            $table->string('category', 50)->default('OTHER')->after('unit');
        });

        $fertilizers = array_map('strtolower', DB::table('fertilizer_types')->whereNotNull('label')->pluck('label')->all());

        foreach (DB::table('price_items')->pluck('item') as $item) {
            $name = strtolower((string) $item);

            $category = match (true) {
                // A label or, for a price item named a little differently (NPK201216, FOMIIMBURA), the NPK and FOMI families
                in_array($name, $fertilizers, true), str_starts_with($name, 'npk'), str_starts_with($name, 'fomi') => 'FERTILIZER',
                $name === 'investment_amount' => 'INVESTMENT',
                $name === 'cassup', str_starts_with($name, 'maize_'), str_starts_with($name, 'sweetpotato_') => 'PRODUCE',
                str_starts_with($name, 'manual_'), str_starts_with($name, 'weeding') => 'LABOUR',
                str_starts_with($name, 'tractor_') => 'MACHINERY',
                default => 'OTHER',
            };

            DB::table('price_items')->where('item', $item)->update(['category' => $category]);
        }

        Schema::table('price_items', function (Blueprint $table) {
            $table->foreign('category')->references('value')->on('price_categories')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('price_items', function (Blueprint $table) {
            $table->dropForeign(['category']);
            $table->dropColumn('category');
        });
    }
};
