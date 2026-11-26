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
 * The release gate of the mobile app per platform (GET /v1/app/version). The values are managed through the API
 * (PUT /v1/admin/app/version): the Android publish workflow raises `latest` after each upload, an admin raises
 * `min_supported`. The migration stores the starting row once (nobody is asked to update).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 20)->unique();
            $table->unsignedInteger('min_supported');
            $table->unsignedInteger('latest');
            $table->string('message', 255)->nullable();
            $table->string('store_url', 255)->nullable();
            $table->timestampsTz();
        });

        DB::table('app_versions')->insert([
            'platform' => 'android',
            'min_supported' => 1,
            'latest' => 1,
            'store_url' => 'https://play.google.com/store/apps/details?id=com.akilimo.mobile',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
