<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Service\RecommendationReportService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['app.url' => 'https://api.example.test', 'akilimo-compute.reports.enabled' => true]);
});

it('passes on a working disk and leaves nothing behind', function () {
    Storage::fake('local');
    config(['akilimo-compute.reports.disk' => 'local']);

    $this->artisan('reports:check-storage')
        ->expectsOutputToContain('Reports disk: local')
        ->expectsOutputToContain('Save a report the way a recommendation does')
        ->expectsOutputToContain('Stream the download')
        ->expectsOutputToContain('Report storage works')
        ->assertSuccessful();

    expect(Storage::disk('local')->allFiles('reports'))->toBe([]);
});

it('keeps the test report with --keep', function () {
    Storage::fake('local');
    config(['akilimo-compute.reports.disk' => 'local']);

    $this->artisan('reports:check-storage', ['--keep' => true])->assertSuccessful();

    expect(Storage::disk('local')->allFiles('reports'))->toHaveCount(2);
});

it('checks the disk named with --disk instead of the configured one', function () {
    Storage::fake('local');
    Storage::fake('other');
    config([
        'akilimo-compute.reports.disk' => 'local',
        'filesystems.disks.other' => ['driver' => 'local', 'root' => storage_path('framework/testing/other')],
    ]);

    $this->artisan('reports:check-storage', ['--disk' => 'other', '--keep' => true])
        ->expectsOutputToContain('Reports disk: other')
        ->assertSuccessful();

    expect(Storage::disk('other')->allFiles('reports'))->toHaveCount(2)
        ->and(Storage::disk('local')->allFiles('reports'))->toBe([]);
});

it('fails for a disk that does not exist', function () {
    $this->artisan('reports:check-storage', ['--disk' => 'nope'])
        ->expectsOutputToContain("There is no disk called 'nope'")
        ->assertFailed();
});

it('names the missing S3 settings and never prints the keys', function () {
    config([
        'akilimo-compute.reports.disk' => 's3',
        'filesystems.disks.s3.key' => 'AKIA-SHOULD-NOT-APPEAR',
        'filesystems.disks.s3.secret' => 'SECRET-SHOULD-NOT-APPEAR',
        'filesystems.disks.s3.bucket' => null,
        'filesystems.disks.s3.endpoint' => 'https://s3.example.test',
    ]);

    $this->artisan('reports:check-storage')
        ->expectsOutputToContain('Set AWS_BUCKET in the .env file')
        ->doesntExpectOutputToContain('AKIA-SHOULD-NOT-APPEAR')
        ->doesntExpectOutputToContain('SECRET-SHOULD-NOT-APPEAR')
        ->assertFailed();
});

it('shows the real storage error when the write fails', function () {
    $broken = Mockery::mock(FilesystemAdapter::class)->shouldIgnoreMissing();
    $broken->shouldReceive('put')->andThrow(new RuntimeException('Access Denied (bucket policy)'));
    Storage::set('broken', $broken);
    config(['filesystems.disks.broken' => ['driver' => 'local', 'root' => storage_path('framework/testing/broken')]]);

    $this->artisan('reports:check-storage', ['--disk' => 'broken'])
        ->expectsOutputToContain('Write a test file: Access Denied (bucket policy)')
        ->expectsOutputToContain('Report storage is NOT working')
        ->assertFailed();
});

it('fails when a disk quietly refuses the write', function () {
    $quiet = Mockery::mock(FilesystemAdapter::class)->shouldIgnoreMissing();
    $quiet->shouldReceive('put')->andReturn(false);
    Storage::set('quiet', $quiet);
    config(['filesystems.disks.quiet' => ['driver' => 'local', 'root' => storage_path('framework/testing/quiet')]]);

    $this->artisan('reports:check-storage', ['--disk' => 'quiet'])
        ->expectsOutputToContain('the disk refused the write')
        ->assertFailed();
});

it('fails when the test report cannot be deleted', function () {
    Storage::fake('local');
    config(['akilimo-compute.reports.disk' => 'local']);
    $real = Storage::disk('local');
    $stuck = Mockery::mock(FilesystemAdapter::class)->shouldIgnoreMissing();
    $stuck->shouldReceive('put')->andReturnUsing(fn (...$a) => $real->put(...$a));
    $stuck->shouldReceive('get')->andReturnUsing(fn (...$a) => $real->get(...$a));
    $stuck->shouldReceive('exists')->andReturnUsing(fn (...$a) => $real->exists(...$a));
    $stuck->shouldReceive('lastModified')->andReturnUsing(fn (...$a) => $real->lastModified(...$a));
    $stuck->shouldReceive('files')->andReturnUsing(fn (...$a) => $real->files(...$a));
    $stuck->shouldReceive('readStream')->andReturnUsing(fn (...$a) => $real->readStream(...$a));
    $stuck->shouldReceive('download')->andReturnUsing(fn (...$a) => $real->download(...$a));
    $stuck->shouldReceive('deleteDirectory')->andThrow(new RuntimeException('Delete not allowed'));
    Storage::set('stuck', $stuck);
    config(['filesystems.disks.stuck' => ['driver' => 'local', 'root' => storage_path('framework/testing/stuck')]]);

    $this->artisan('reports:check-storage', ['--disk' => 'stuck'])
        ->expectsOutputToContain('Delete the test report: Delete not allowed')
        ->assertFailed();
});

it('fails an S3 bucket that answers without credentials', function () {
    Storage::fake('s3check', ['url' => 'https://s3.example.test/reports-bucket']);
    config([
        'akilimo-compute.reports.disk' => 's3check',
        'filesystems.disks.s3check.driver' => 's3',
        'filesystems.disks.s3check.key' => 'k',
        'filesystems.disks.s3check.secret' => 's',
        'filesystems.disks.s3check.bucket' => 'reports-bucket',
        'filesystems.disks.s3check.endpoint' => 'https://s3.example.test',
    ]);
    Http::fake(['s3.example.test/*' => Http::response('public', 200)]);

    $this->artisan('reports:check-storage')
        ->expectsOutputToContain('can be read WITHOUT credentials')
        ->assertFailed();
});

it('passes an S3 bucket that refuses anonymous requests', function () {
    Storage::fake('s3check', ['url' => 'https://s3.example.test/reports-bucket']);
    config([
        'akilimo-compute.reports.disk' => 's3check',
        'filesystems.disks.s3check.driver' => 's3',
        'filesystems.disks.s3check.key' => 'k',
        'filesystems.disks.s3check.secret' => 's',
        'filesystems.disks.s3check.bucket' => 'reports-bucket',
        'filesystems.disks.s3check.endpoint' => 'https://s3.example.test',
    ]);
    Http::fake(['s3.example.test/*' => Http::response('denied', 403)]);

    $this->artisan('reports:check-storage')
        ->expectsOutputToContain('Check the object is not readable without credentials')
        ->assertSuccessful();
});

it('keeps a storage failure from breaking report saving', function () {
    $broken = Mockery::mock(FilesystemAdapter::class)->shouldIgnoreMissing();
    $broken->shouldReceive('put')->andThrow(new RuntimeException('Access Denied'));
    Storage::set('broken', $broken);
    config(['akilimo-compute.reports.disk' => 'broken']);
    Log::spy();

    $stored = app(RecommendationReportService::class)->store((string)Str::uuid(), [[
        'name' => 'fertilizer_advice.pdf',
        'content_type' => 'application/pdf',
        'content' => base64_encode("%PDF-1.4\n%%EOF"),
    ]]);

    expect($stored)->toBe([]);
    Log::shouldHaveReceived('error')->withArgs(fn ($message, $context) => $message === 'Recommendation report could not be stored'
        && $context['error'] === 'Access Denied'
        && !array_key_exists('content', $context));
});

it('has safe defaults on the S3 disk', function () {
    $s3 = config('filesystems.disks.s3');

    expect($s3['visibility'])->toBe('private')
        ->and($s3['throw'])->toBeTrue()
        ->and($s3['use_path_style_endpoint'])->toBeBool()
        ->and($s3['region'])->not->toBeEmpty()
        ->and($s3['http']['connect_timeout'])->toBeLessThanOrEqual(10)
        ->and($s3['http']['timeout'])->toBeLessThanOrEqual(60);
});
