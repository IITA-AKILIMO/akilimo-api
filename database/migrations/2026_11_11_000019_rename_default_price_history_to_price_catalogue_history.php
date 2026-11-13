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
 * The history of the price catalogue follows the table's new name: default_price_history becomes
 * price_catalogue_history, and its default_price_id column becomes price_catalogue_id, so the table, the
 * model and the key read as one thing. Rows, ids and the foreign key to price_catalogue are unchanged (the
 * foreign key follows both renames), and the history written by earlier migrations is kept.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::rename('default_price_history', 'price_catalogue_history');

        Schema::table('price_catalogue_history', function (Blueprint $table) {
            $table->renameColumn('default_price_id', 'price_catalogue_id');
        });
    }

    public function down(): void
    {
        Schema::table('price_catalogue_history', function (Blueprint $table) {
            $table->renameColumn('price_catalogue_id', 'default_price_id');
        });

        Schema::rename('price_catalogue_history', 'default_price_history');
    }
};
