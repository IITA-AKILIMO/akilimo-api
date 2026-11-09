<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class RecommendationException extends Exception
{
    /**
     * Structured error data returned from the API or service.
     */
    public readonly array $body;

    /**
     * Create a new recommendation exception instance.
     *
     * @param string $message Exception message
     * @param int $code HTTP status code (default: 500)
     * @param array $body Structured error data from the API
     * @param Throwable|null $previous Previous exception for chaining
     */
    public function __construct(
        string $message = '',
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $body = [],
        ?Throwable $previous = null,
    ) {
        // Extract nested data if present, otherwise use body as-is
        $this->body = Arr::get($body, 'data', $body);

        // Determine the final message
        $finalMessage = $message ?:
            Arr::get($this->body, 'message') ?:
                'An error occurred while processing the recommendation';

        // Ensure valid HTTP status code
        $finalCode = $this->normalizeStatusCode($code);

        parent::__construct($finalMessage, $finalCode, $previous);
    }

    /**
     * Normalize the HTTP status code to ensure it's valid.
     */
    private function normalizeStatusCode(int $code): int
    {
        // Ensure code is within valid HTTP status code range
        if ($code < 100 || $code > 599) {
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $code;
    }

    /**
     * Render the exception as a JSON HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        $status = $this->getCode();

        $response = [
            'error' => $this->getErrorType(),
            'message' => $this->getMessage(),
            'status' => $status,
        ];

        if (!empty($this->body['errors'])) {
            $response['errors'] = $this->body['errors'];
        }

        return response()->json($response, $status);
    }

    /**
     * Get a human-readable error type based on status code.
     */
    private function getErrorType(): string
    {
        return match ($this->getCode()) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            default => Arr::get($this->body, 'error', 'Recommendation Error'),
        };
    }

    /**
     * Create an exception for when a recommendation is not found.
     *
     * @return static
     */
    public static function notFound(string $message = 'Recommendation not found'): RecommendationException
    {
        return new self($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Create an exception for service unavailability.
     *
     * @return static
     */
    public static function serviceUnavailable(
        string $message = 'Recommendation service is currently unavailable',
        array $body = [],
        ?Throwable $previous = null,
    ): RecommendationException {
        return new self($message, Response::HTTP_SERVICE_UNAVAILABLE, $body, $previous);
    }

    /**
     * Create an exception for invalid data.
     */
    public static function invalidData(string $message = 'Invalid recommendation data', array $errors = []): static
    {
        return new self($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    public static function validationFailed(array $errors, string $message = 'Validation failed'): static
    {
        return new self(
            $message,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            ['errors' => $errors]
        );
    }
}
