<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Keeps the PDF reports the compute service returns and hands out signed download links.
 *
 * Reports live on a private disk in one flat folder, one file per report: reports/{kind}-{report id}.pdf, e.g.
 * reports/fertilizer_advice-123e4567-e89b-12d3-a456-426614174000.pdf. The report id is the request_id of the
 * computation in api_requests, so a file can be traced to its request from its name alone. (Before this, reports
 * were kept as reports/{report id}/{file}; those are still read, so old links keep working, and are never written.)
 * They are kept as long as that history (`keep_days`, empty = forever). A link only lasts `link_ttl_days`; a new one
 * can be made at any time (GET /v1/recommendations/reports/{id}, the admin request log), so an old link in someone's
 * saved history is renewed rather than lost. Links are signed relative to the site and put on APP_URL, so they work
 * whether the API was called on its public address or internally (the MCP server calls it at http://akilimo-api/api).
 *
 * The compute service makes four kinds of PDF, listed in FILES. A report is found by its name, so a new kind has to be
 * added there (a report with another name is skipped, with a warning in the log).
 */
class RecommendationReportService
{
    private const DIR = 'reports';

    /** The PDFs the compute service makes: the names it gives them, which are also the names in the download links */
    public const FILES = ['fertilizer_advice.pdf', 'intercrop_advice.pdf', 'PP_advice.pdf', 'SP_advice.pdf'];

    /**
     * The disk the reports are kept on: the one named in AKILIMO_REPORTS_DISK, else the app's default disk
     * (FILESYSTEM_DISK), else `local`. A `public` default is replaced by `local`: its files are served to anyone, and a
     * report holds the farmer's name, masked phone number and farm location.
     */
    public static function resolveDisk(?string $configured, ?string $default): string
    {
        $configured = trim((string)$configured);
        if ($configured !== '') {
            return $configured;
        }

        $default = trim((string)$default);

        return ($default === '' || $default === 'public') ? 'local' : $default;
    }

    public function enabled(): bool
    {
        return (bool)config('akilimo-compute.reports.enabled', true);
    }

    /**
     * Save the reports of one computation. Entries that are not a valid PDF, or not one of FILES, are skipped.
     *
     * @param array<int, array{name?: string, content_type?: string, content?: string}> $reports
     * @return array<int, string> the stored file names (as in FILES)
     */
    public function store(string $reportId, array $reports): array
    {
        $stored = [];
        $maxBytes = (int)config('akilimo-compute.reports.max_bytes', 10 * 1024 * 1024);

        foreach ($reports as $report) {
            $name = $this->knownName((string)($report['name'] ?? ''));
            $bytes = base64_decode((string)($report['content'] ?? ''), true);

            if ($name === null || $bytes === false || !str_starts_with($bytes, '%PDF') || strlen($bytes) > $maxBytes) {
                Log::warning('Recommendation report skipped', ['report_id' => $reportId, 'name' => $report['name'] ?? null]);

                continue;
            }

            // A storage failure never holds back the recommendation: the S3 disk throws on errors, which is caught
            // here and logged (the message only, never the PDF), and the file is left out of the result
            try {
                $this->disk()->put($this->path($reportId, $name), $bytes);
            } catch (\Throwable $e) {
                Log::error('Recommendation report could not be stored', [
                    'report_id' => $reportId,
                    'name' => $name,
                    'disk' => config('akilimo-compute.reports.disk', 'local'),
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            $stored[] = $name;
        }

        return $stored;
    }

    /**
     * Fresh download links of a stored report, or null when it has none (never made, or deleted). Pass `$files`
     * (what store() returned) when you have it: otherwise the store is asked for each kind of PDF in turn.
     *
     * @param array<int, string>|null $files
     * @return array{id: string, expires_at: string, url: string, files: array<int, array{name: string, url: string}>}|null
     */
    public function links(string $reportId, ?array $files = null): ?array
    {
        $files ??= $this->files($reportId);
        if ($files === []) {
            return null;
        }

        $expiresAt = now()->addDays(max(1, (int)config('akilimo-compute.reports.link_ttl_days', 7)));
        $entries = array_map(fn (string $file) => [
            'name' => $file,
            'url' => $this->url($reportId, $file, $expiresAt),
        ], $files);

        return [
            'id' => $reportId,
            'expires_at' => $expiresAt->toIso8601String(),
            'url' => $entries[0]['url'],
            'files' => $entries,
        ];
    }

    /** @return array<int, string> the PDF names stored for a report, sorted */
    public function files(string $reportId): array
    {
        if (!Str::isUuid($reportId)) {
            return [];
        }

        $found = array_values(array_filter(self::FILES, fn (string $name) => $this->has($this->path($reportId, $name))));
        if ($found === []) {
            $found = $this->legacyNames($reportId);   // a report from before the flat layout
        }
        sort($found);

        return $found;
    }

    /** The PDF as a download (streamed from whichever disk holds it), or null when there is no such file */
    public function download(string $reportId, string $file): ?StreamedResponse
    {
        $path = $this->locate($reportId, $file);
        if ($path === null) {
            return null;
        }

        return $this->disk()->download($path, $this->downloadName($reportId, $file, $path), [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    /** A file name a farmer can recognise later: what the advice is and the day it was made, e.g. akilimo-fertilizer-advice-2026-11-05.pdf */
    public function downloadName(string $reportId, string $file, ?string $path = null): string
    {
        $kind = match (strtolower(pathinfo($file, PATHINFO_FILENAME))) {
            'fertilizer_advice' => 'fertilizer-advice',
            'intercrop_advice' => 'intercropping-advice',
            'pp_advice' => 'planting-practices-advice',
            'sp_advice' => 'planting-dates-advice',
            default => Str::slug(pathinfo($file, PATHINFO_FILENAME)) ?: 'advice',
        };
        try {
            $made = date('Y-m-d', $this->disk()->lastModified($path ?? $this->path($reportId, $file)));
        } catch (\Throwable) {
            $made = now()->toDateString(); // no such file, or the store would not say: today
        }

        return "akilimo-{$kind}-{$made}.pdf";
    }

    /** Delete every file of a report, in either layout */
    public function forget(string $reportId): void
    {
        if (!Str::isUuid($reportId)) {
            return;
        }

        $this->disk()->delete(array_map(fn (string $name) => $this->path($reportId, $name), self::FILES));
        $this->disk()->deleteDirectory(self::DIR . '/' . $reportId);
    }

    /**
     * Delete reports older than `keep_days`; returns how many were removed. Without `keep_days` (the default)
     * reports are kept as long as the recommendation history and nothing is deleted.
     */
    public function prune(): int
    {
        $keepDays = (int)config('akilimo-compute.reports.keep_days');
        if ($keepDays <= 0) {
            return 0;
        }

        $cutoff = now()->subDays($keepDays)->getTimestamp();
        $removed = 0;

        // The flat layout: group the files by the report id in their name (only files named like this service's)
        $byReport = [];
        foreach ($this->disk()->files(self::DIR) as $path) {
            if (preg_match('/^[A-Za-z_]+-([0-9a-fA-F-]{36})\.pdf$/', basename($path), $m) === 1) {
                $byReport[$m[1]][] = $path;
            }
        }
        foreach ($byReport as $paths) {
            $newest = max(array_map(fn (string $path) => $this->disk()->lastModified($path), $paths));
            if ($newest < $cutoff) {
                $this->disk()->delete($paths);
                $removed++;
            }
        }

        // The old layout: one folder per report
        foreach ($this->disk()->directories(self::DIR) as $dir) {
            $files = $this->disk()->files($dir);
            $newest = array_reduce($files, fn (int $max, string $f) => max($max, $this->disk()->lastModified($f)), 0);
            if ($newest < $cutoff) {
                $this->disk()->deleteDirectory($dir);
                $removed++;
            }
        }

        return $removed;
    }

    private function url(string $reportId, string $file, \DateTimeInterface $expiresAt): string
    {
        $relative = URL::temporarySignedRoute('recommendations.report', $expiresAt, ['report' => $reportId, 'file' => $file], absolute: false);

        return rtrim((string)config('app.url'), '/') . $relative;
    }

    /** Only a plain file name ending in .pdf, so a name from the compute service cannot leave the report folder */
    private function safeName(string $name): ?string
    {
        $name = basename($name);

        return preg_match('/^[A-Za-z0-9_.-]{1,100}\.pdf$/', $name) === 1 ? $name : null;
    }

    /** The name in FILES that this one is (the case does not matter: PP_advice.pdf, pp_advice.pdf), or null */
    private function knownName(string $name): ?string
    {
        $name = $this->safeName($name);
        if ($name === null) {
            return null;
        }
        foreach (self::FILES as $known) {
            if (strcasecmp($known, $name) === 0) {
                return $known;
            }
        }

        return null;
    }

    /** Where a report is kept: reports/{kind}-{report id}.pdf, the kind in lower case */
    private function path(string $reportId, string $file): string
    {
        return self::DIR . '/' . strtolower(pathinfo($file, PATHINFO_FILENAME)) . '-' . $reportId . '.pdf';
    }

    /** Where reports used to be kept (one folder per report): read, never written */
    private function legacyNames(string $reportId): array
    {
        return collect($this->disk()->files(self::DIR . '/' . $reportId))
            ->map(fn (string $path) => basename($path))
            ->filter(fn (string $name) => $this->safeName($name) !== null)
            ->values()
            ->all();
    }

    /** The path of a stored report file in whichever layout holds it, or null when it is not there */
    private function locate(string $reportId, string $file): ?string
    {
        $name = $this->safeName($file);
        if (!Str::isUuid($reportId) || $name === null) {
            return null;
        }

        $known = $this->knownName($name);
        if ($known !== null && $this->has($this->path($reportId, $known))) {
            return $this->path($reportId, $known);
        }

        return in_array($name, $this->legacyNames($reportId), true) ? self::DIR . '/' . $reportId . '/' . $name : null;
    }

    /**
     * Is there a file at this path? Asked with size() and not exists(): some S3-compatible stores (Alarik) answer the
     * HEAD request behind exists() with 403 even for a file they hold, but allow the one behind size().
     */
    private function has(string $path): bool
    {
        try {
            $this->disk()->size($path);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk(config('akilimo-compute.reports.disk', 'local'));
    }
}
