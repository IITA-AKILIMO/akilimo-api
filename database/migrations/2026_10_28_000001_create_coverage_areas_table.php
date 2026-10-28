<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The area each country's recommendations cover, as GeoJSON, ingested from the compute service
 * (GET /coverage/{country}) by `php artisan coverage:sync`. The map draws it, and compute requests are
 * checked against it before they reach the (slow) compute service.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('coverage_areas', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2)->unique();
            // FeatureCollection exactly as the compute service sent it (EPSG:4326, [lon, lat])
            $table->longText('geojson');
            $table->unsignedInteger('n_cells')->nullable();
            // Bounding box for a quick "clearly outside" answer before the polygon test
            $table->double('min_lon');
            $table->double('min_lat');
            $table->double('max_lon');
            $table->double('max_lat');
            // SHA-256 of the GeoJSON: a sync that finds the same hash changes nothing
            $table->char('checksum', 64);
            $table->timestamp('fetched_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coverage_areas');
    }
};
