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
 * country_fertilizers.use_case becomes a foreign key to fertilizer_use_cases, so
 * the database refuses a use case that is not in the list and follows a rename of
 * one. Every value in use was added to the list by the previous migration.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('country_fertilizers', function (Blueprint $table) {
            $table->foreign('use_case')->references('value')->on('fertilizer_use_cases')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('country_fertilizers', function (Blueprint $table) {
            $table->dropForeign(['use_case']);
        });
    }
};
