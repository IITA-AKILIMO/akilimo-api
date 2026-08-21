<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Logging;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class CustomLoggerService
{
    protected LoggerInterface $logger;

    /**
     * @param array|string|null $channels One or more log channels (stacked or single)
     */
    public function __construct(array|string|null $channels = null)
    {
        $this->logger = $this->resolveLogger($channels);
    }

    /**
     * Resolve the logger instance based on the channel(s).
     */
    protected function resolveLogger(array|string|null $channels): LoggerInterface
    {
        // Fallback to default Laravel channel if none provided
        if ($channels === []) {
            return Log::channel(config('logging.default'));
        }

        // If arrayed, use the stack driver
        if (is_array($channels)) {
            return Log::stack($channels);
        }

        // If strung, treat it as a single named channel
        return Log::channel($channels);
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
