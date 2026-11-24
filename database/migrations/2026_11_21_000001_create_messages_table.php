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
 * Messages sent to the mobile app. The API owns them: an admin writes one, the API stores it and announces it through
 * FCM (topics `country_<CODE>` and `lang_<code>`), and the app also fetches what it missed (phone off, push switched off,
 * reinstall) from GET /v1/messages. A message without a country or a language is for everyone in that dimension.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            // The id travels in the FCM data message and in the sync, so the app stores a message once however it arrives
            $table->uuid('id')->primary();
            $table->string('type', 20);
            $table->string('country', 2)->nullable();
            $table->string('language', 8)->nullable();
            $table->string('title', 120);
            $table->text('body');
            $table->string('target', 20)->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('pushed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();

            $table->index(['created_at', 'country', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
