<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Data\AkilimoComputeData;
use App\Exceptions\RecommendationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AkilimoComputeService
{
    protected string $baseUrl;

    protected string $endpoint;

    protected int $timeout;

    protected int $retries;

    protected bool $logging;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('akilimo-compute.base_url'), '/');
        $this->endpoint = ltrim(config('akilimo-compute.endpoint'), '/');
        $this->timeout = config('akilimo-compute.timeout', 120);
        $this->retries = config('akilimo-compute.retries', 3);
        $this->logging = config('akilimo-compute.logging', true);

    }

    /**
     * Send compute request to Akilimo API.
     *
     * @throws ConnectionException|RecommendationException|RequestException
     */
    public function compute(AkilimoComputeData $data): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->retry($this->retries)
                ->acceptJson()
                ->post($this->endpoint, $data->toArray());
        } catch (ConnectionException $e) {
            Log::error('AKILIMO Compute API Connection Error', ['endpoint' => $this->endpoint, 'error' => $e->getMessage()]);
            throw $e;
        } catch (RequestException $e) {
            // HTTP-level error after retries — let the caller handle status code mapping
            throw $e;
        } catch (\Throwable $e) {
            Log::error('AKILIMO API Unexpected Error', ['endpoint' => $this->endpoint, 'error' => $e->getMessage()]);
            throw new RecommendationException('Unexpected error calling AKILIMO Compute API', 500);
        }

        $body = $response->json();

        if ($body === null) {
            Log::error('AKILIMO API Invalid JSON', [
                'status_code' => $response->status(),
                'raw_body' => $response->body(),
                'endpoint' => $this->endpoint,
            ]);
            throw new RecommendationException('Invalid JSON response from AKILIMO Compute API', $response->status());
        }

        if ($response->successful()) {
            $this->log('info', 'AKILIMO API Success', $response);

            return $body;
        }

        $message = $body['message']
            ?? $body['error']
            ?? $body['error_message']
            ?? $body['errorMessage']
            ?? "Failed to call AKILIMO API (HTTP {$response->status()})";

        $this->log('error', 'AKILIMO API Error', $response, $message, $body);

        throw new RecommendationException($message, $response->status());
    }

    /**
     * The area the compute service covers for one country, as GeoJSON (GET /coverage/{country}).
     *
     * @return array<string, mixed> a FeatureCollection
     *
     * @throws ConnectionException|RequestException|RecommendationException
     */
    public function coverage(string $country): array
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry(2, 500)
            ->accept('application/geo+json,application/json')
            ->get('coverage/' . strtoupper($country))
            ->throw();

        $body = $response->json();

        if (!is_array($body) || !isset($body['type'])) {
            throw new RecommendationException("The compute service sent no GeoJSON for coverage of {$country}", 502);
        }

        return $body;
    }

    protected function log(string $level, string $context, $response, ?string $message = null, ?array $body = null): void
    {
        if (!$this->logging) {
            return;
        }

        Log::$level($context, [
            'endpoint' => $this->endpoint,
            'status_code' => $response->status(),
            'message' => $message,
            'body' => $body,
        ]);
    }
}
