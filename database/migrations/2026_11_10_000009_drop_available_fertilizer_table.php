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
 * available_fertilizer is an old fertiliser list (with a stray price column of
 * 0.00 and country "ALL"), superseded by the fertilizers table that carries
 * fertilizer_key. Nothing read it; its model is removed with it.
 *
 * down() recreates the empty table.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('available_fertilizer');
    }

    public function down(): void
    {
        Schema::create('available_fertilizer', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('type', 50);
            $table->integer('n_content');
            $table->integer('p_content');
            $table->integer('k_content');
            $table->integer('weight')->default(50);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('country', 5)->default('ALL');
            $table->string('use_case', 5)->nullable()->default('ALL');
            $table->boolean('available')->nullable()->default(true);
            $table->boolean('custom')->nullable()->default(true);
            $table->integer('sort_order')->nullable()->default(999);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent();
        });
    }
};
