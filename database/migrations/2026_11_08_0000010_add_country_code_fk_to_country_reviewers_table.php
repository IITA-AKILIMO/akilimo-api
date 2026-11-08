<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('country_reviewers', function (Blueprint $table) {
            // Adjust 'country' to your actual column type if it isn't an unsigned big integer
            // (e.g., use string('country') or foreignUuid() if it's a string/uuid).
            $table->foreign('country')
                ->references('code')
                ->on('countries')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('country_reviewers', function (Blueprint $table) {
            $table->dropForeign(['country']);
        });
    }
};
