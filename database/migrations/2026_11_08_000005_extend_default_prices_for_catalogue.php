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
 * Phase 1 of the price catalogue (docs/PRICE_CATALOGUE_DESIGN.md).
 *
 * Extends the existing `default_prices` table in place so the engine's current
 * sync keeps working, and adds an audit trail for every change.
 *
 * Existing rows are marked `active`: they are the values the engine already
 * uses. Rows created through the admin or imported from the research drafts
 * start as `draft` and must be reviewed before they are served by
 * GET /v1/prices or GET /v1/default-prices.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('default_prices', function (Blueprint $table) {
            $table->string('region', 50)->nullable()->after('country');
            $table->double('subsidised_price')->nullable()->after('price');
            $table->unsignedSmallInteger('bag_kg')->nullable()->after('unit');
            $table->double('min_price')->nullable()->after('bag_kg');
            $table->double('max_price')->nullable()->after('min_price');
            $table->string('status', 25)->default('active')->after('max_price');
            $table->string('source', 255)->nullable()->after('status');
            $table->date('source_date')->nullable()->after('source');
            $table->string('confidence', 10)->nullable()->after('source_date');
            $table->date('effective_from')->nullable()->after('confidence');
            $table->foreignId('updated_by')->nullable()->after('effective_from')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->after('updated_by')
                ->constrained('users')->nullOnDelete();

            $table->index(['country', 'status']);
            $table->index(['status', 'effective_from']);
        });

        Schema::create('default_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('default_price_id')->nullable()
                ->constrained('default_prices')->nullOnDelete();
            $table->string('country', 2);
            $table->string('item', 50);
            $table->string('region', 50)->nullable();
            $table->string('action', 30);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('changed_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['country', 'item']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('default_price_history');

        Schema::table('default_prices', function (Blueprint $table) {
            $table->dropIndex(['country', 'status']);
            $table->dropIndex(['status', 'effective_from']);
            $table->dropConstrainedForeignId('submitted_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn([
                'region',
                'subsidised_price',
                'bag_kg',
                'min_price',
                'max_price',
                'status',
                'source',
                'source_date',
                'confidence',
                'effective_from',
            ]);
        });
    }
};
