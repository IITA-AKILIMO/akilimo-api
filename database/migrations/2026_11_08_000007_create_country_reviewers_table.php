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
 * Country teams assign the admins who may review and activate that country's
 * prices (decision D4). Until a country has a reviewer, any admin can review.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('country_reviewers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('country', 2);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['country', 'user_id']);
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_reviewers');
    }
};
