<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Console\Commands;

use App\Service\RecommendationReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Throwable;

/**
 * Proves the reports disk works before real PDFs depend on it: saves a tiny test PDF the way a recommendation does,
 * reads it back, makes a signed link, streams the download, checks an S3 bucket is not readable without credentials,
 * then deletes the test file. Nothing but a fake one-page PDF is written, and the keys are never printed.
 */
class CheckReportStorage extends Command
{
    protected $signature = 'reports:check-storage
        {--disk= : Check this disk instead of akilimo-compute.reports.disk}
        {--keep : Leave the test report in place (it is removed by default)}';

    protected $description = 'Check that PDF reports can be saved, read, linked and deleted on the reports disk (run after changing AKILIMO_REPORTS_DISK or the S3 settings)';

    private const PDF = "%PDF-1.4\n% AKILIMO storage check\n%%EOF";

    private const NAME = 'storage-check.pdf';

    private bool $failed = false;

    public function handle(RecommendationReportService $reports): int
    {
        $name = (string)($this->option('disk') ?: config('akilimo-compute.reports.disk', 'local'));
        config(['akilimo-compute.reports.disk' => $name]);

        $this->line("Reports disk: <info>{$name}</info>");
        if (!config('akilimo-compute.reports.enabled', true)) {
            $this->warn('AKILIMO_REPORTS_ENABLED is false: reports are switched off, so nothing is stored in practice.');
        }

        if (!$this->checkSettings($name)) {
            return self::FAILURE;
        }

        $disk = Storage::disk($name);
        $id = (string)Str::uuid();
        $folder = "reports/{$id}";

        try {
            // 1. The raw disk: shows the real error (bad keys, wrong endpoint, no such bucket) that store() only logs
            $wrote = $this->step('Write a test file', function () use ($disk, $folder) {
                $this->must($disk->put("{$folder}/raw.txt", 'akilimo storage check') !== false, 'the disk refused the write (it returned false without an error)');
            });
            if ($wrote) {
                $this->step('Read it back', function () use ($disk, $folder) {
                    $this->must($disk->get("{$folder}/raw.txt") === 'akilimo storage check', 'the file read back differs from what was written');
                });
                $this->step('Read its modification time (used for the download name)', function () use ($disk, $folder) {
                    $this->must($disk->lastModified("{$folder}/raw.txt") > 0, 'no modification time');
                });
            }

            // 2. The real code path a recommendation takes
            if ($wrote) {
                $stored = $this->step('Save a report the way a recommendation does', function () use ($reports, $id) {
                    $names = $reports->store($id, [[
                        'name' => self::NAME,
                        'content_type' => 'application/pdf',
                        'content' => base64_encode(self::PDF),
                    ]]);
                    $this->must($names === [self::NAME], 'the report was not stored (the reason is in the application log)');
                });

                if ($stored) {
                    $this->step('Find it again (files)', function () use ($reports, $id) {
                        $this->must($reports->files($id) === [self::NAME], 'the stored report is not listed');
                    });
                    $this->step('Make a signed download link', function () use ($reports, $id) {
                        $links = $reports->links($id);
                        $this->must($links !== null && str_contains($links['url'], 'signature='), 'no signed link was made');
                        $this->line('     link host: ' . (parse_url($links['url'], PHP_URL_HOST) ?: '?') . ' (from APP_URL)');
                    });
                    $this->step('Stream the download', function () use ($reports, $id) {
                        $response = $reports->download($id, self::NAME);
                        $this->must($response !== null, 'download() found no file');
                        ob_start();
                        $response->sendContent();
                        $body = (string)ob_get_clean();
                        $this->must($body === self::PDF, 'the downloaded bytes differ from what was stored');
                    });
                }
            }

            // 3. A private bucket must not answer without credentials: the PDFs hold personal data
            if ($wrote && config("filesystems.disks.{$name}.driver") === 's3') {
                $this->step('Check the object is not readable without credentials', function () use ($disk, $folder) {
                    $url = $disk->url("{$folder}/raw.txt");
                    try {
                        $status = Http::timeout(10)->withoutRedirecting()->get($url)->status();
                    } catch (Throwable $e) {
                        $this->warn('     could not test anonymous access (' . Str::limit($e->getMessage(), 120) . ')');

                        return;
                    }
                    $this->must($status !== 200, 'the object can be read WITHOUT credentials: make the bucket private');
                });
            }
        } finally {
            if ($wrote ?? false) {
                $this->cleanUp($disk, $folder);
            }
        }

        $this->newLine();
        if ($this->failed) {
            $this->error('Report storage is NOT working.');

            return self::FAILURE;
        }

        $this->info('Report storage works: reports can be saved, linked, downloaded and deleted.');

        return self::SUCCESS;
    }

    /** The disk exists and, for S3, has what it needs. Shows the settings, never the keys. */
    private function checkSettings(string $name): bool
    {
        $config = config("filesystems.disks.{$name}");
        if (!is_array($config)) {
            $this->failure("There is no disk called '{$name}' in config/filesystems.php.");

            return false;
        }

        $driver = (string)($config['driver'] ?? '?');
        $this->line("Driver: {$driver}");
        if ($driver !== 's3') {
            if ($driver === 'local') {
                $this->line('Root: ' . ($config['root'] ?? '?') . ' (needs a persistent volume in Docker)');
            }

            return true;
        }

        $required = [
            'key' => 'AWS_ACCESS_KEY_ID',
            'secret' => 'AWS_SECRET_ACCESS_KEY',
            'bucket' => 'AWS_BUCKET',
        ];
        $missing = collect($required)->filter(fn (string $env, string $key) => empty($config[$key]))->values()->all();

        $this->line('Bucket: ' . ($config['bucket'] ?? null ?: '<comment>not set</comment>'));
        $this->line('Endpoint: ' . ($config['endpoint'] ?? null ?: 'AWS default'));
        $this->line('Region: ' . ($config['region'] ?? null ?: 'not set') . ', path-style: ' . (!empty($config['use_path_style_endpoint']) ? 'yes' : 'no'));
        $this->line('Access key: ' . (empty($config['key']) ? 'MISSING' : 'set') . ', secret: ' . (empty($config['secret']) ? 'MISSING' : 'set'));

        if ($missing !== []) {
            $this->failure('Set ' . implode(', ', $missing) . ' in the .env file.');

            return false;
        }

        if (!class_exists(AwsS3V3Adapter::class)) {
            $this->failure('The S3 driver is not installed: run `composer require league/flysystem-aws-s3-v3`.');

            return false;
        }

        if (empty($config['endpoint'])) {
            $this->warn('AWS_ENDPOINT is empty: this talks to AWS itself. For a self-hosted store (Alarik) set AWS_ENDPOINT.');
        }

        return true;
    }

    /** Run one check; print OK or the reason it failed. Returns whether it passed. */
    private function step(string $label, callable $check): bool
    {
        $start = microtime(true);
        try {
            $check();
        } catch (Throwable $e) {
            $this->failure("{$label}: " . Str::limit(trim($e->getMessage()), 300));

            return false;
        }

        $this->line(sprintf('<info>OK</info>   %s (%d ms)', $label, (microtime(true) - $start) * 1000));

        return true;
    }

    private function must(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    private function failure(string $message): void
    {
        $this->failed = true;
        $this->line("<error>FAIL</error> {$message}");
    }

    /** Remove the test report, unless --keep; a failed delete is itself a failed check. */
    private function cleanUp($disk, string $folder): void
    {
        if ($this->option('keep')) {
            $this->line("Test report kept at {$folder}/ on the disk.");

            return;
        }

        try {
            $disk->deleteDirectory($folder);
            // listed, not exists(): some S3-compatible stores answer exists()'s HEAD request with 403
            $gone = $disk->files($folder) === [];
            if ($gone) {
                $this->line('<info>OK</info>   Delete the test report');
            } else {
                $this->failure("Delete the test report: files are still there under {$folder}/ (remove them by hand).");
            }
        } catch (Throwable $e) {
            $this->failure('Delete the test report: ' . Str::limit(trim($e->getMessage()), 300) . " (remove {$folder}/ by hand)");
        }
    }
}
