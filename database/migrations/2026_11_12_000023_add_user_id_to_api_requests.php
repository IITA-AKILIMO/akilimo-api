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
 * Farmer accounts (docs/FARMER_ACCOUNTS_DESIGN.md, step 1), part 3 of 3: saved advice.
 *
 * The account a saved recommendation belongs to. The link is set to null when the account is deleted,
 * so the request itself survives for statistics.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('api_requests', function (Blueprint $table) {
            // Same type as users.id (unsigned big integer) so it can be a foreign key
            $table->unsignedBigInteger('user_id')->nullable()->after('device_token')->index();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('api_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
