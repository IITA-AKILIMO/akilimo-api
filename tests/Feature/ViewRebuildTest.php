<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Support\ViewRebuilder;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// The views are defined by the files in database/views and made (again) by `php artisan views:rebuild`, which
// also runs after every migration, so a view follows a table that gained or lost a column.

it('has a file for every view, and a view for every file', function () {
    $views = app(ViewRebuilder::class);

    expect(array_keys($views->definitions()))->toEqualCanonicalizing($views->existingViews());
    expect($views->check())->toBe(['missing' => [], 'broken' => [], 'unknown' => [], 'unprefixed' => []]);
});

it('rebuilds every view from its file', function () {
    $this->artisan('views:rebuild')->assertSuccessful();

    expect(app(ViewRebuilder::class)->existingViews())->toHaveCount(8);
});

it('says which view is missing, and rebuilds it', function () {
    DB::statement('DROP VIEW vw_potato_prices');

    $this->artisan('views:rebuild', ['--check' => true])
        ->expectsOutputToContain('vw_potato_prices has a file but no view')
        ->assertFailed();

    $this->artisan('views:rebuild')->assertSuccessful();
    $this->artisan('views:rebuild', ['--check' => true])->assertSuccessful();
});

it('rebuilds the views by itself after the migrations', function () {
    DB::statement('DROP VIEW vw_potato_prices');
    DB::statement('DROP VIEW vw_maize_prices');

    event(new MigrationsEnded('up'));

    expect(app(ViewRebuilder::class)->existingViews())->toContain('vw_potato_prices', 'vw_maize_prices');
});

it('reports a view a changed table has broken, keeps it as it was, and rebuilds it once its file is fixed', function () {
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'views_' . str()->random(8);
    File::makeDirectory($dir);
    $views = new ViewRebuilder($dir);

    try {
        DB::statement('CREATE TABLE tmp_view_source (a INT, b INT)');
        DB::table('tmp_view_source')->insert(['a' => 1, 'b' => 2]);
        File::put($dir . '/vw_tmp_check.sql', "-- a test view\nCREATE OR REPLACE VIEW `vw_tmp_check` AS\nselect a, b from tmp_view_source;\n");

        expect($views->rebuild())->toBe(['rebuilt' => ['vw_tmp_check'], 'failed' => []]);
        expect((array) DB::table('vw_tmp_check')->first())->toBe(['a' => 1, 'b' => 2]);

        // a column the view reads is dropped: the view stops working, and the check says so
        DB::statement('ALTER TABLE tmp_view_source DROP COLUMN b');
        expect($views->check()['broken'])->toHaveKey('vw_tmp_check');

        // the file still asks for b, so the rebuild fails, and says why, and nothing was dropped
        $result = $views->rebuild();
        expect($result['rebuilt'])->toBe([])
            ->and($result['failed'])->toHaveKey('vw_tmp_check')
            ->and($views->existingViews())->toContain('vw_tmp_check');

        // the file is changed to the new column, and the rebuild makes the view again
        File::put($dir . '/vw_tmp_check.sql', "CREATE OR REPLACE VIEW `vw_tmp_check` AS\nselect a from tmp_view_source;\n");
        expect($views->rebuild()['failed'])->toBe([]);
        // (the real views have no file in this temporary folder, so only the view under test is judged)
        expect($views->check()['missing'])->toBe([])->and($views->check()['broken'])->toBe([]);
        expect((array) DB::table('vw_tmp_check')->first())->toBe(['a' => 1]);
    } finally {
        DB::statement('DROP VIEW IF EXISTS vw_tmp_check');
        DB::statement('DROP TABLE IF EXISTS tmp_view_source');
        File::deleteDirectory($dir);
    }
});

it('rebuilds a view that reads another view whatever order they come in', function () {
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'views_' . str()->random(8);
    File::makeDirectory($dir);

    try {
        DB::statement('CREATE TABLE tmp_view_source (a INT)');
        // sorted by name the dependent view comes first, so the first pass fails for it and the second succeeds
        File::put($dir . '/vw_tmp_a_second.sql', "CREATE OR REPLACE VIEW `vw_tmp_a_second` AS\nselect a from vw_tmp_b_first;\n");
        File::put($dir . '/vw_tmp_b_first.sql', "CREATE OR REPLACE VIEW `vw_tmp_b_first` AS\nselect a from tmp_view_source;\n");

        $result = (new ViewRebuilder($dir))->rebuild();

        expect($result['failed'])->toBe([])
            ->and($result['rebuilt'])->toEqualCanonicalizing(['vw_tmp_a_second', 'vw_tmp_b_first']);
    } finally {
        DB::statement('DROP VIEW IF EXISTS vw_tmp_a_second');
        DB::statement('DROP VIEW IF EXISTS vw_tmp_b_first');
        DB::statement('DROP TABLE IF EXISTS tmp_view_source');
        File::deleteDirectory($dir);
    }
});
