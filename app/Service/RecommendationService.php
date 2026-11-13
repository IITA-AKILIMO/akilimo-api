<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Data\AkilimoComputeData;
use App\Data\ComputeRequestData;
use App\Data\FertilizerData;
use App\Data\UserInfoData;
use App\Exceptions\RecommendationException;
use App\Repositories\ApiRequestRepo;
use App\Repositories\FertilizerRepo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RecommendationService
{
    protected int|\DateTimeInterface $cacheTTL;

    public function __construct(
        protected FertilizerRepo $fertilizerRepo,
        protected ApiRequestRepo $apiRequestRepo,
        protected AkilimoComputeService $akilimoComputeService,
        protected ?TrafficExclusionService $trafficExclusions = null,
        protected ?RecommendationReportService $reports = null,
    ) {
        $ttl = config('cache.ttl'); // e.g. "1d", "1h", "30m"
        $this->cacheTTL = $this->parseTtl($ttl);
    }

    /**
     * Handles the entire recommendation computation workflow.
     *
     * @param array $droidRequest The parsed request payload.
     * @return array Contains 'rec_type' and 'recommendation'.
     *
     * @throws \JsonException
     */
    public function compute(array $droidRequest, ?int $userId = null): array
    {
        // A farmer with an account keeps a saved row of their own, so their result is cached apart from the shared one:
        // a shared hit would log nothing for them, and the row it was logged under belongs to someone else.
        $cacheKey = $this->generateCacheKey($droidRequest) . ($userId !== null ? ":user:{$userId}" : '');
        $result = Cache::remember($cacheKey, $this->cacheTTL, function () use ($droidRequest, $userId) {
            return $this->performComputation($droidRequest, $userId);
        });

        // Anonymous calls always get a fresh request_id: each HTTP call is a distinct trace event even when the
        // result is served from cache. A signed-in farmer keeps the id of their saved row, so the app can match its
        // copy with GET /v1/account/history/{request_id} (a repeat of the same inputs is that same saved advice).
        if ($userId === null) {
            $result['request_id'] = (string)Str::uuid();
        }

        // The cache is shared by everyone with the same farm inputs, but a PDF names the farmer:
        // a cached report is only handed out to the same person, never to someone else.
        $stored = Arr::pull($result, '_report');
        $result['report'] = $stored !== null && hash_equals($stored['owner'], $this->reportOwner($droidRequest))
            ? $this->reportService()->links($stored['id'], $stored['files'] ?? null)
            : null;

        return $result;
    }

    private function reportService(): RecommendationReportService
    {
        return $this->reports ??= app(RecommendationReportService::class);
    }

    /** The personal details printed on a report; two requests share a report only when these match */
    private function reportOwner(array $droidRequest): string
    {
        $user = Arr::get($droidRequest, 'user_info', []);
        $fields = Arr::only(is_array($user) ? $user : [], ['user_name', 'first_name', 'last_name', 'email_address', 'phone_number', 'farm_name', 'device_token']);
        ksort($fields);

        return hash('sha256', json_encode($fields));
    }

    /**
     * Performs computation based on the droid request data.
     *
     * @param array $droidRequest The input data containing user information, compute request, and fertilizer list.
     * @return array An array containing the recommended type and recommendations.
     *
     * @throws RecommendationException If there is an error specific to the recommendation process.
     * @throws Exception If a general exception occurs during the computation process.
     */
    private function performComputation(array $droidRequest, ?int $userId = null): array
    {
        $userInfoArray = Arr::get($droidRequest, 'user_info', []);
        $computeRequestArray = Arr::get($droidRequest, 'compute_request', []);
        $fertilizerList = Arr::get($droidRequest, 'fertilizer_list', []);
        $deviceToken = Arr::get($userInfoArray, 'device_token', 'NA');

        $userInfo = UserInfoData::from($userInfoArray);

        $computeRequest = ComputeRequestData::from($computeRequestArray);
        $availableFertilizers = FertilizerData::collect($this->getAvailableFertilizers($computeRequest->farmInformation->countryCode));
        $requestedFertilizers = FertilizerData::collect($fertilizerList);
        $fertilizerMap = collect($requestedFertilizers)->keyBy('key');

        $computedFertilizers = $this->mapFertilizersToExternalFormat($availableFertilizers, $fertilizerMap);

        // Fertilizer and intercropping advice choose among fertilizers. An empty catalogue for the country
        // (fertilizers view without rows) would make the compute service fail with a bare 500, so stop here and say why.
        if ($computedFertilizers === [] && ($computeRequest->recommendations->fertilizerRec || $computeRequest->interCropping->isRecommended())) {
            \Log::error('No fertilizers are configured for the requested country', [
                'country' => $computeRequest->farmInformation->countryCode,
            ]);

            throw RecommendationException::serviceUnavailable(
                "No fertilizers are configured for {$computeRequest->farmInformation->countryCode}",
                ['error' => 'No fertilizers configured', 'country' => $computeRequest->farmInformation->countryCode],
            );
        }

        $akilimoComputeData = AkilimoComputeData::from(
            $computeRequest->toArray(),
            $userInfo,
            ['fertilizers' => $computedFertilizers],
        );
        $akilimoComputeData->returnPdf = $this->reportService()->enabled();

        // Generate a server-side UUID as the request correlation ID (#38).
        // The client-supplied device_token is stored separately for device-level queries.
        $requestUuid = (string)Str::uuid();
        $akilimoComputeData->requestId = $requestUuid; // the report's printed reference
        $requestLog = $this->logRequest($requestUuid, $deviceToken, $droidRequest, $akilimoComputeData, $userId);

        $startedAt = Carbon::now();

        try {
            $computeResp = $this->akilimoComputeService->compute($akilimoComputeData);

            // The PDFs (base64) go to the report store, never into the request log or the cache
            $reports = Arr::pull($computeResp, 'reports');
            $files = is_array($reports) && $reports !== [] ? $this->reportService()->store($requestUuid, $reports) : [];

            $this->updateRequestLogResponse($requestLog->id, $computeResp, $startedAt);

            return [
                '_report' => $files === [] ? null : [
                    'id' => $requestUuid,
                    'files' => $files,   // kept with the cached answer, so a later link needs no look-up in the store
                    'owner' => $this->reportOwner($droidRequest),
                ],
                'request_id' => $requestUuid,
                'status' => Arr::get($computeResp, 'status', 'success'),
                'version' => Arr::get($computeResp, 'version'),
                'data' => [
                    'rec_type' => Arr::get($computeResp, 'data.rec_type'),
                    'recommendation' => Arr::get($computeResp, 'data.recommendation'),
                    'data' => Arr::get($computeResp, 'data.data', []),
                    'fertilizer_rates' => Arr::get($computeResp, 'data.fertilizer_rates', []),
                ],
            ];
        } catch (RecommendationException $ex) {
            $this->updateRequestLogResponse($requestLog->id, $ex->body, $startedAt);
            throw $ex;
        } catch (ConnectionException $ex) {
            // Network/timeout/DNS failures
            $errorBody = [
                'error' => 'Connection failed',
                'message' => 'Unable to connect to recommendation service',
                'details' => $ex->getMessage(),
            ];

            $this->updateRequestLogResponse($requestLog->id, $errorBody, $startedAt);

            throw RecommendationException::serviceUnavailable('Recommendation service is unreachable');
        } catch (RequestException $ex) {
            // HTTP errors (4xx, 5xx responses)
            $statusCode = $ex->response->status();
            $responseBody = $ex->response->json() ?? [];

            $errorBody = [
                'error' => 'Service request failed',
                'message' => $ex->getMessage(),
                'status_code' => $statusCode,
                'response' => $responseBody,
            ];

            $this->updateRequestLogResponse($requestLog->id, $errorBody, $startedAt);

            throw new RecommendationException(
                Arr::get($responseBody, 'message', 'Recommendation service returned an error'),
                $statusCode,
                $errorBody,
                $ex
            );
        } catch (Exception $ex) {
            // Log the full trace so unexpected bugs surface in production rather than being
            // silently swallowed. The client still receives a clean 500 response.
            \Log::error('Unexpected exception during recommendation computation', [
                'exception' => get_class($ex),
                'message' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'trace' => $ex->getTraceAsString(),
            ]);

            $errorBody = [
                'error' => 'Unexpected error',
                'message' => $ex->getMessage(),
                'type' => get_class($ex),
            ];

            $this->updateRequestLogResponse($requestLog->id, $errorBody, $startedAt);

            throw new RecommendationException(
                $ex->getMessage() ?: 'Failed to compute recommendation',
                500,
                $errorBody,
                $ex
            );
        }

    }

    /**
     * Generates a unique cache key based on the droidRequest payload.
     *
     * @throws \JsonException
     */
    private function generateCacheKey(array $droidRequest): string
    {
        $relevantData = [
            // user_info excluded intentionally: email, phone, and device_token do not affect
            // the computation result and would prevent cache reuse across users with identical inputs.
            'compute_request' => Arr::get($droidRequest, 'compute_request', []),
            'fertilizer_list' => Arr::get($droidRequest, 'fertilizer_list', []),
        ];

        // Sort recursively for consistent ordering
        ksort($relevantData);
        array_walk_recursive($relevantData, function (&$value, $key) {
            if (is_array($value)) {
                ksort($value);
            }
        });

        // Use SHA-256 or xxHash (faster for non-crypto use)
        return 'rec:' . hash('sha256', json_encode($relevantData, JSON_THROW_ON_ERROR));
    }

    /**
     * Retrieves available fertilizers for a specific country.
     */
    private function getAvailableFertilizers(string $countryCode): \Illuminate\Database\Eloquent\Collection
    {
        return $this->fertilizerRepo->selectByCondition(
            conditions: [
                'country' => $countryCode,
                'available' => true,
            ],
            columns: [
                'fertilizer_label as fertilizer_label',
                'name as name',
                'weight as weight',
                'fertilizer_key as key',
                'type as fertilizer_type',
            ]
        );
    }

    /**
     * Maps fertilizers to the external service format.
     */
    private function mapFertilizersToExternalFormat(Collection $availableFertilizers, Collection $fertilizerMap): array
    {
        $computedFertilizers = [];

        foreach ($availableFertilizers as $fertilizer) {
            $key = $fertilizer->key ?? null;
            $label = $fertilizer->label ?? null;

            // Skip if key or label missing (data integrity)
            if (!$key || !$label) {
                continue;
            }

            $selected = $fertilizer->selected ?? false;
            $weight = $fertilizer->weight ?? 0;
            $price = $fertilizer->pricePerBag ?? 0;

            if ($fertilizerMap->has($key)) {
                $matched = $fertilizerMap->get($key);
                $selected = $matched->selected ?? $selected;
                $weight = $matched->weight ?? $weight;
                $price = $matched->pricePerBag ?? $price;
            }

            $computedFertilizers["{$label}available"] = $selected;
            $computedFertilizers["{$label}BagWt"] = $weight;
            $computedFertilizers["{$label}CostperBag"] = $price;
        }

        return $computedFertilizers;
    }

    /**
     * Logs the incoming request to the database.
     *
     * @return object The created log entry.
     */
    private function logRequest(string $requestUuid, string $deviceToken, array $droidRequest, AkilimoComputeData $plumberRequest, ?int $userId = null): object
    {
        $log = $this->apiRequestRepo->create([
            'request_id' => $requestUuid,
            'device_token' => $deviceToken,
            'user_id' => $userId,
            'droid_request' => $droidRequest,
            'plumber_request' => $plumberRequest,
        ]);

        // Staff and test traffic is flagged from the admin-managed rules
        ($this->trafficExclusions ?? app(TrafficExclusionService::class))->refresh($log->id);

        return $log;
    }

    /**
     * Make the PDF report of a logged request again, from the exact payload that was sent then (plumber_request),
     * e.g. after the report template improved or when the request predates stored reports. The recommendation,
     * the request log and the cache are left as they are; only the stored PDF is replaced.
     *
     * @return array|null the new report links, or null when the compute service made no report
     *
     * @throws RecommendationException|ConnectionException|RequestException
     */
    public function regenerateReport(string $requestId, array $plumberRequest): ?array
    {
        // The log keeps the fertilizers nested, as AkilimoComputeData holds them; the service expects them at the top
        $payload = Arr::except($plumberRequest, ['fertilizers']) + (array)($plumberRequest['fertilizers'] ?? []);
        $payload = array_merge($payload, ['returnPdf' => true, 'email' => false, 'SMS' => false, 'requestId' => $requestId]);

        $reports = Arr::get($this->akilimoComputeService->computePayload($payload), 'reports');
        $files = is_array($reports) && $reports !== [] ? $this->reportService()->store($requestId, $reports) : [];
        if ($files === []) {
            return null;
        }

        return $this->reportService()->links($requestId, $files);
    }

    /**
     * Updates the log entry with the Plumbr response and computed latency.
     *
     * @param Carbon $startedAt Time immediately before the Plumbr call
     */
    private function updateRequestLogResponse(int $logId, array $responseData, Carbon $startedAt): void
    {
        $this->apiRequestRepo->update($logId, [
            'plumber_response' => $responseData,
            'request_started_at' => $startedAt,
            'request_duration_ms' => (int)$startedAt->diffInMilliseconds(now()),
        ]);
    }

    /**
     * Parses a TTL (time-to-live) string and converts it to a Carbon instance.
     *
     * @param string $ttl The TTL string to parse. It can be a numeric value in seconds
     *                    or a string with a unit (e.g., "10d" for 10 days, "5h" for 5 hours, "30m" for 30 minutes).
     * @return Carbon The calculated expiration time as a Carbon instance.
     */
    private function parseTtl(string $ttl): Carbon
    {

        $now = Carbon::now();

        if (is_numeric($ttl)) {
            return $now->addSeconds((int)$ttl);
        }

        if (preg_match('/^(\d+)([sdhm])$/', $ttl, $matches)) {
            $value = (int)$matches[1];
            $unit = $matches[2];

            return match ($unit) {
                's' => $now->addSeconds($value),
                'd' => $now->addDays($value),
                'h' => $now->addHours($value),
                'm' => $now->addMinutes($value),
            };
        }
        \Log::warning('Unrecognised CACHE_TTL value, defaulting to 1 hour', ['value' => $ttl]);

        return $now->addHour();
    }
}
