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
 * Reports live on a private disk under reports/{report id}/{file}, where the report id is the request_id of the
 * computation in api_requests, and are kept as long as that history (`keep_days`, empty = forever). A link only
 * lasts `link_ttl_days`; a new one can be made at any time (GET /v1/recommendations/reports/{id}, the admin
 * request log), so an old link in someone's saved history is renewed rather than lost. Links are signed relative
 * to the site and put on APP_URL, so they work whether the API was called on its public address or internally
 * (the MCP server calls it at http://akilimo-api/api).
 */
class RecommendationReportService
{
    private const DIR = 'reports';

    public function enabled(): bool
    {
        return (bool)config('akilimo-compute.reports.enabled', true);
    }

    /**
     * Save the reports of one computation. Entries that are not a valid PDF are skipped.
     *
     * @param array<int, array{name?: string, content_type?: string, content?: string}> $reports
     * @return array<int, string> the stored file names
     */
    public function store(string $reportId, array $reports): array
    {
        $stored = [];
        $maxBytes = (int)config('akilimo-compute.reports.max_bytes', 10 * 1024 * 1024);

        foreach ($reports as $report) {
            $name = $this->safeName((string)($report['name'] ?? ''));
            $bytes = base64_decode((string)($report['content'] ?? ''), true);

            if ($name === null || $bytes === false || !str_starts_with($bytes, '%PDF') || strlen($bytes) > $maxBytes) {
                Log::warning('Recommendation report skipped', ['report_id' => $reportId, 'name' => $report['name'] ?? null]);

                continue;
            }

            $this->disk()->put($this->path($reportId, $name), $bytes);
            $stored[] = $name;
        }

        return $stored;
    }

    /**
     * Fresh download links of a stored report, or null when it has none (never made, or deleted).
     *
     * @return array{id: string, expires_at: string, url: string, files: array<int, array{name: string, url: string}>}|null
     */
    public function links(string $reportId): ?array
    {
        $files = $this->files($reportId);
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

    /** @return array<int, string> the PDF names stored for a report, oldest first */
    public function files(string $reportId): array
    {
        if (!Str::isUuid($reportId)) {
            return [];
        }

        return collect($this->disk()->files(self::DIR . '/' . $reportId))
            ->map(fn (string $path) => basename($path))
            ->filter(fn (string $name) => $this->safeName($name) !== null)
            ->sort()
            ->values()
            ->all();
    }

    /** The PDF as a download (streamed from whichever disk holds it), or null when there is no such file */
    public function download(string $reportId, string $file): ?StreamedResponse
    {
        $name = $this->safeName($file);
        if (!Str::isUuid($reportId) || $name === null || !$this->disk()->exists($this->path($reportId, $name))) {
            return null;
        }

        return $this->disk()->download($this->path($reportId, $name), $this->downloadName($reportId, $name), [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    /** A file name a farmer can recognise later: what the advice is and the day it was made, e.g. akilimo-fertilizer-advice-2026-11-05.pdf */
    public function downloadName(string $reportId, string $file): string
    {
        $kind = match (strtolower(pathinfo($file, PATHINFO_FILENAME))) {
            'fertilizer_advice' => 'fertilizer-advice',
            'intercrop_advice' => 'intercropping-advice',
            'pp_advice' => 'planting-practices-advice',
            'sp_advice' => 'planting-dates-advice',
            default => Str::slug(pathinfo($file, PATHINFO_FILENAME)) ?: 'advice',
        };
        $made = $this->disk()->exists($this->path($reportId, $file))
            ? date('Y-m-d', $this->disk()->lastModified($this->path($reportId, $file)))
            : now()->toDateString();

        return "akilimo-{$kind}-{$made}.pdf";
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

    private function path(string $reportId, string $file): string
    {
        return self::DIR . '/' . $reportId . '/' . $file;
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk(config('akilimo-compute.reports.disk', 'local'));
    }
}
