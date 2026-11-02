<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Service\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class McpKeyController extends Controller
{
    public function __construct(protected ApiKeyService $keys) {}

    /**
     * Verify MCP Key
     *
     * Called by the AKILIMO MCP server (akilimo-mcp-agent) with its own key, which needs the
     * `mcp:verify-keys` ability, to check a key a user sent to it. A key is valid when it is
     * active, not expired and has `mcp:use` (users create such keys in the playground under
     * API keys, type "MCP key"). Always answers 200: `valid` false says nothing about why.
     */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:200'],
        ]);

        $key = $this->keys->verifyMcpKey($data['key']);

        if ($key === null) {
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid' => true,
            'key_id' => $key->id,
            'key_prefix' => $key->key_prefix,
            'user' => [
                'id' => $key->user?->id,
                'username' => $key->user?->username,
            ],
        ]);
    }
}
