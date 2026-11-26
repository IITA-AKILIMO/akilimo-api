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
 * A message may be private to one phone (`recipient_device_token`) or to one farmer account (`recipient_user_id`, every
 * phone signed in to it). Such a message is never returned by GET /v1/messages to anyone else. With neither set it is a
 * broadcast for a country and/or language, as before.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('recipient_device_token', 255)->nullable()->after('target')->index();
            $table->foreignId('recipient_user_id')->nullable()->after('recipient_device_token')->constrained('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipient_user_id');
            $table->dropIndex(['recipient_device_token']);
            $table->dropColumn('recipient_device_token');
        });
    }
};
