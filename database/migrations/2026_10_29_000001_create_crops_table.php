<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The crops AKILIMO knows about, managed in the admin area and listed by GET /v1/crops so clients (the
 * MCP agent, the apps) offer a crop choice from data instead of a fixed list. Only cassava has
 * recommendations today: `advice_available` says whether the compute service can give advice for a crop,
 * so a crop can be listed (for prices and information) before its models exist.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            // Stable identifier clients send and store (lowercase, a-z 0-9 and underscores)
            $table->string('crop_key', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('advice_available')->default(false);
            // ISO country codes where the crop is offered, as a JSON list; null means every country
            $table->json('countries')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();
        });

        // The one crop that works today, so the list is never empty after migrating
        DB::table('crops')->insert([
            'crop_key' => 'cassava',
            'name' => 'Cassava',
            'description' => 'Fertilizer, planting practices, planting and harvest dates, and intercropping advice',
            'active' => true,
            'advice_available' => true,
            'countries' => null,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};
