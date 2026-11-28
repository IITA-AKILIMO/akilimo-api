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
 * Phones that can be pushed to one by one. `device_token` is the install id the app already sends with account and
 * compute requests; `fcm_token` is the Firebase registration token the app registers with PUT /v1/devices (and replaces
 * when Firebase rotates it). A phone signed in to a farmer account carries that account's id.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('push_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_token', 255)->unique();
            $table->string('fcm_token', 512)->index();
            $table->string('platform', 10)->default('android');
            $table->string('app_version', 20)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('language', 8)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('last_seen_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_devices');
    }
};
