<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Jobs\Core;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Base class of every queued job: the traits a job needs and one place for the retry settings and for what is logged when
 * a job has failed for good. A job sets only what differs ($tries, $backoff, $timeout, the failure log) and adds its own
 * constructor and handle().
 */
abstract class AbstractJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** Attempts in all (the first run and two retries). A job that must never run twice sets 1. */
    public int $tries = 3;

    /** Exceptions allowed across the attempts; the same as $tries, so every attempt may fail before the job is failed. */
    public int $maxExceptions = 3;

    /**
     * Seconds a run may take before the worker stops it. It must stay below the queue's `retry_after` (90 s for redis,
     * REDIS_QUEUE_RETRY_AFTER): a job that runs longer is handed to a second worker while the first still works on it.
     * A job that needs longer raises both.
     */
    public int $timeout = 60;

    /**
     * Seconds to wait before each retry, by attempt number: one value per retry, so $tries - 1 values.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 60];

    /** Rows per chunk for jobs that walk a large table. */
    protected int $chunkSize = 500;

    /** Log level and message of a job that failed for good; a job may lower the level for failures that are expected. */
    protected string $failureLevel = 'critical';

    protected string $failureMessage = 'Job failed';

    /** What a job adds to its failure log entry (ids, never personal data). @return array<string, mixed> */
    protected function failureContext(): array
    {
        return [];
    }

    /** Handle a job that has failed for good (all attempts used). */
    public function failed(Throwable $exception): void
    {
        Log::log($this->failureLevel, $this->failureMessage, [
            'job' => static::class,
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            ...$this->failureContext(),
        ]);
    }
}
