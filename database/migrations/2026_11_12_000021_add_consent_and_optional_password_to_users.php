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
 * Farmer accounts (docs/FARMER_ACCOUNTS_DESIGN.md, step 1), part 1 of 3: the users table.
 *
 * A password that may be empty (a social-only account has none), and the time and wording version of the farmer's consent.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->timestamp('consent_at')->nullable()->after('email_verified_at');
            $table->string('consent_version', 20)->nullable()->after('consent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['consent_at', 'consent_version']);
            // A password cannot be required again while social-only accounts may exist
        });
    }
};
