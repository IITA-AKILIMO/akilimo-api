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
 * The outcome of a message's push: queued, pushed, failed or not_pushed (App\Enums\PushStatus), with the reason a push
 * failed. The push job updates it, so it follows what really happened. Earlier messages are marked from `pushed_at`.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('push_status', 12)->default('not_pushed')->after('pushed_at')->index();
            $table->string('push_error', 255)->nullable()->after('push_status');
        });

        DB::table('messages')->whereNotNull('pushed_at')->update(['push_status' => 'pushed']);
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['push_status']);
            $table->dropColumn(['push_status', 'push_error']);
        });
    }
};
