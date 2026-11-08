<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('users')->where('role', 'playground')->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', EnumUserRole::values())
                ->default(EnumUserRole::User)->change();
        });
    }

    public function down(): void
    {

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 15)->default('playground')->change();
        });

        DB::table('users')->where('role', 'user')->update(['role' => 'playground']);
    }
};
