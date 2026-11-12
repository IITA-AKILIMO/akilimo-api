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
 * Farmer accounts (docs/FARMER_ACCOUNTS_DESIGN.md, step 1).
 *
 * users: a password that may be empty (a social-only account has none), and the time and wording version of the farmer's consent.
 *
 * user_identities: the social sign-ins (Google, Facebook, ...) linked to an account. One table for every provider, so a provider
 * is added without a migration and an account may link several.
 *
 * api_requests: the account a saved recommendation belongs to. The link is set to null when the
 * account is deleted, so the request itself survives for statistics.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->timestamp('consent_at')->nullable()->after('email_verified_at');
            $table->string('consent_version', 20)->nullable()->after('consent_at');
        });

        Schema::create('user_identities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider', 32);
            $table->string('subject', 191); // the provider's own id for the person
            $table->timestamps();

            $table->unique(['provider', 'subject']);
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

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

        Schema::dropIfExists('user_identities');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['consent_at', 'consent_version']);
            // A password cannot be required again while Google-only accounts may exist
        });
    }
};
