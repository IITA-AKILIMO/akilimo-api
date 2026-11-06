<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Auth\TokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiKeyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ApiKeyController extends Controller
{
    /**
     * List all API keys for the authenticated user.
     * The full key is never returned here — only metadata and the prefix.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $keys = $request->user()
            ->apiKeys()
            ->orderByDesc('created_at')
            ->get();

        return ApiKeyResource::collection($keys);
    }

    /**
     * Generate a new API key for the authenticated user.
     *
     * The full raw key is returned ONCE in the response and never stored.
     * The caller must copy it immediately.
     *
     * @throws ValidationException|\Random\RandomException
     */
    public function store(Request $request): JsonResponse
    {
        $validAbilities = implode(',', TokenAbility::ALL);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', "in:{$validAbilities}"],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        // A new key can never do more than the token or key that creates it; no abilities = wildcard
        $caller = $request->attributes->get('auth_token');
        $requested = ($validated['abilities'] ?? null) ?: [TokenAbility::WILDCARD];
        $beyond = array_values(array_filter($requested, fn (string $ability) => !$caller?->can($ability)));
        if ($beyond !== []) {
            throw ValidationException::withMessages([
                'abilities' => 'You cannot grant abilities you do not have: ' . implode(', ', $beyond) . '.',
            ]);
        }

        $rawKey = 'ak_' . bin2hex(random_bytes(16)); // ak_ + 32 hex chars = 35 chars total
        $prefix = substr($rawKey, 0, 12);             // "ak_" + first 8 hex chars
        $hash = hash('sha256', $rawKey);

        $apiKey = $request->user()->apiKeys()->create([
            'name' => $validated['name'],
            'key_prefix' => $prefix,
            'key_hash' => $hash,
            'abilities' => $validated['abilities'] ?? null, // null = wildcard
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        /**
         * @status 201
         */
        return response()->json([
            'data' => [
                ...(new ApiKeyResource($apiKey))->toArray($request),
                'key' => $rawKey, // full key — shown once, never retrievable again
            ],
            'message' => 'Store this key securely. It will not be shown again.',
        ], 201);
    }

    /**
     * Revoke (soft-disable) an API key.
     * The key row is kept so last_used_at history is preserved.
     */
    public function revoke(Request $request, int $id): JsonResponse
    {
        $apiKey = $request->user()
            ->apiKeys()
            ->findOrFail($id);

        $apiKey->update(['is_active' => false]);

        return response()->json([
            'data' => new ApiKeyResource($apiKey->fresh()),
            'message' => 'API key revoked.',
        ]);
    }

    /**
     * Permanently delete an API key.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->user()
            ->apiKeys()
            ->findOrFail($id)
            ->delete();

        return response()->json(null, 204);
    }
}
