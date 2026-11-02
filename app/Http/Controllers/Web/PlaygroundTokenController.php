<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Web;

use App\Auth\TokenAbility;
use App\Enums\EnumUserRole as UserRole;
use App\Models\ApiKey;
use App\Service\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PlaygroundTokenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $keys = ApiKey::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'key_prefix', 'is_active', 'abilities', 'last_used_at', 'expires_at', 'created_at']);

        return response()->json($keys->map(fn ($k) => [
            'id' => $k->id,
            'name' => $k->name,
            'key_prefix' => $k->key_prefix,
            'is_active' => $k->is_active,
            'abilities' => $k->abilities ?? ['*'],
            'last_used_at' => $k->last_used_at?->toIso8601String(),
            'expires_at' => $k->expires_at?->toIso8601String(),
            'created_at' => $k->created_at?->toIso8601String(),
        ]));
    }

    public function store(Request $request, ApiKeyService $keys): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            // "mcp": a key for the AKILIMO MCP server only (mcp:use), whatever the user's role
            'type' => ['sometimes', 'string', 'in:api,mcp'],
        ]);

        $user = $request->user();

        $abilities = ($data['type'] ?? 'api') === 'mcp'
            ? TokenAbility::MCP_ABILITIES
            : match ($user->role) {
                UserRole::Admin => [TokenAbility::WILDCARD],
                UserRole::Partner => TokenAbility::PARTNER_ABILITIES,
                default => TokenAbility::PLAYGROUND_ABILITIES,
            };

        ['key' => $key, 'raw' => $rawKey] = $keys->issue($user, $data['name'], $abilities);

        return response()->json([
            'id' => $key->id,
            'name' => $key->name,
            'key' => $rawKey,
            'key_prefix' => $key->key_prefix,
            'is_active' => true,
            'abilities' => $key->abilities,
            'created_at' => $key->created_at?->toIso8601String(),
        ], 201);
    }

    public function revoke(Request $request, int $id): JsonResponse
    {
        $key = ApiKey::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $key->update(['is_active' => false]);

        return response()->json(['message' => 'API key revoked.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $key = ApiKey::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $key->delete();

        return response()->json(['message' => 'API key deleted.']);
    }
}
