<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('DATABASECHANGELOG');
        Schema::dropIfExists('DATABASECHANGELOGLOCK');
        Schema::dropIfExists('users_old');
    }
};
