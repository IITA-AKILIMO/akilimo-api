<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Logging;

use Monolog\Level;
use Monolog\Logger;

class LokiLoggerFactory
{
    public function __invoke(array $config): Logger
    {
        $handler = new LokiHandler(
            url: $config['with']['url'] ?? '',
            username: $config['with']['username'] ?? '',
            password: $config['with']['password'] ?? '',
            labels: $config['with']['labels'] ?? [],
            job: $config['with']['job'] ?? 'laravel',
            level: $config['level'] ?? Level::Debug
        );

        if (isset($config['formatter'])) {
            $formatter = app($config['formatter']);
            $handler->setFormatter($formatter);
        }

        return new Logger('loki', [$handler]);
    }
}
