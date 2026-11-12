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
 * Farmer accounts (docs/FARMER_ACCOUNTS_DESIGN.md, step 1), part 2 of 3: social sign-ins.
 *
 * The social sign-ins (Google, Facebook, ...) linked to an account. One table for every provider, so a provider
 * is added without a migration and an account may link several.
 */
return new class extends Migration {
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('user_identities');
    }
};
