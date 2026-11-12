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
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    $stuck->shouldReceive('size')->andReturnUsing(fn (...$a) => $real->size(...$a));
    $stuck->shouldReceive('lastModified')->andReturnUsing(fn (...$a) => $real->lastModified(...$a));
    $stuck->shouldReceive('files')->andReturnUsing(fn (...$a) => $real->files(...$a));
    $stuck->shouldReceive('readStream')->andReturnUsing(fn (...$a) => $real->readStream(...$a));
    $stuck->shouldReceive('download')->andReturnUsing(fn (...$a) => $real->download(...$a));
    $stuck->shouldReceive('delete')->andThrow(new RuntimeException('Delete not allowed'));
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

it('keeps reports on the configured disk, else the default disk, never a public one', function () {
    $resolve = fn (?string $configured, ?string $default) => RecommendationReportService::resolveDisk($configured, $default);

    expect($resolve('s3', 'local'))->toBe('s3')              // AKILIMO_REPORTS_DISK wins
        ->and($resolve(null, 's3'))->toBe('s3')              // else FILESYSTEM_DISK
        ->and($resolve('', 's3'))->toBe('s3')                // an empty value counts as not set
        ->and($resolve('  ', ' s3 '))->toBe('s3')
        ->and($resolve(null, 'local'))->toBe('local')
        ->and($resolve(null, null))->toBe('local')           // nothing set
        ->and($resolve(null, ''))->toBe('local')
        ->and($resolve(null, 'public'))->toBe('local')       // files on `public` are served to anyone
        ->and($resolve('public', 'public'))->toBe('public'); // but a disk named for reports on purpose is respected
});

it('downloads from a store that answers the exists() request with 403', function () {
    $id = (string)Str::uuid();
    $store = Mockery::mock(FilesystemAdapter::class)->shouldIgnoreMissing();
    $store->shouldReceive('size')->with("reports/fertilizer_advice-{$id}.pdf")->andReturn(14);   // only this file is there
    $store->shouldReceive('size')->andThrow(new RuntimeException('404 Not Found'));
    $store->shouldReceive('files')->andReturn([]);
    $store->shouldNotReceive('exists');                       // Alarik refuses HeadObject with 403
    $store->shouldReceive('lastModified')->andThrow(new RuntimeException('403 Forbidden'));
    $store->shouldReceive('download')->andReturn(new StreamedResponse(fn () => print ('PDF')));
    Storage::set('no-head', $store);
    config(['akilimo-compute.reports.disk' => 'no-head']);
    $reports = app(RecommendationReportService::class);

    expect($reports->download($id, 'fertilizer_advice.pdf'))->toBeInstanceOf(StreamedResponse::class)
        ->and($reports->download($id, 'intercrop_advice.pdf'))->toBeNull()   // not stored
        ->and($reports->download($id, 'notes.txt'))->toBeNull()             // not a PDF name
        ->and($reports->download('not-a-uuid', 'fertilizer_advice.pdf'))->toBeNull()
        ->and($reports->files($id))->toBe(['fertilizer_advice.pdf'])
        ->and($reports->downloadName($id, 'fertilizer_advice.pdf'))
        ->toBe('akilimo-fertilizer-advice-' . now()->toDateString() . '.pdf');   // no date from the store: today
});

it('finishes the check on a store that refuses exists()', function () {
    Storage::fake('local');
    $real = Storage::disk('local');
    $noHead = Mockery::mock(FilesystemAdapter::class)->shouldIgnoreMissing();
    foreach (['put', 'get', 'size', 'files', 'readStream', 'download', 'lastModified', 'delete', 'deleteDirectory'] as $call) {
        $noHead->shouldReceive($call)->andReturnUsing(fn (...$a) => $real->{$call}(...$a));
    }
    $noHead->shouldReceive('exists')->andThrow(new RuntimeException('403 Forbidden'));
    Storage::set('no-head', $noHead);
    config(['filesystems.disks.no-head' => ['driver' => 'local', 'root' => storage_path('framework/testing/no-head')]]);

    $this->artisan('reports:check-storage', ['--disk' => 'no-head'])
        ->expectsOutputToContain('Stream the download')
        ->expectsOutputToContain('Report storage works')
        ->assertSuccessful();
});
