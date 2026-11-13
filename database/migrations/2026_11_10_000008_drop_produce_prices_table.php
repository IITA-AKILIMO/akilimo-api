<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * produce_prices was a second, generic copy of the range price lists (cassava,
 * maize, potato per country). Nothing read it: no route, screen or query, only a
 * seeder and a model, both removed with it. The app's lists are the views and
 * tables named for each crop (docs/PRICE_TABLES_REVIEW.md).
 *
 * down() recreates the empty table; its rows were a copy of data held elsewhere.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('produce_prices');
    }

    public function down(): void
    {
        Schema::create('produce_prices', function (Blueprint $table) {
            $table->id();
            $table->string('country', 4);
            $table->string('produce_name', 20);
            $table->decimal('min_price', 10, 3)->default(0);
            $table->decimal('max_price', 10, 3)->default(0);
            $table->boolean('is_min_price')->default(false);
            $table->boolean('is_max_price')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }
};
