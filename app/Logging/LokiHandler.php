<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Logging;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class LokiHandler extends AbstractProcessingHandler
{
    protected Client $client;

    protected string $url;

    protected string $username;

    protected string $password;

    protected string $job;

    protected array $labels;

    public function __construct(
        string $url,
        string $username = '',
        string $password = '',
        array $labels = [],
        string $job = 'laravel',
        Level $level = Level::Debug,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);

        $this->url = rtrim($url, '/');
        $this->username = $username;
        $this->password = $password;
        $this->job = $job;
        $this->labels = $labels;

        $this->client = new Client;
    }

    /**
     * @throws GuzzleException
     */
    protected function write(array|LogRecord $record): void
    {
        try {
            $timestamp = Carbon::now()->timestamp * 1_000_000_000;
            $formatted = $this->getFormatter()->format($record);

            $logEntry = [
                'streams' => [
                    [
                        'stream' => array_merge([
                            'job' => $this->job,
                            'level' => strtolower($record['level_name']),
                        ], $this->labels),
                        'values' => [
                            [
                                (string)$timestamp,
                                $formatted,
                            ],
                        ],
                    ],
                ],
            ];

            $this->client->post($this->url . '/loki/api/v1/push', [
                'auth' => [$this->username, $this->password],
                'json' => $logEntry,
                'headers' => ['Content-Type' => 'application/json'],
            ]);
        } catch (RequestException $e) {
            Log::channel('daily')->error('Loki push failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
