# MCP keys

The AKILIMO MCP server (**Akilimo 1.0**, repository `akilimo-mcp-agent`) exposes this API's data and recommendations as tools for MCP clients such as Claude Desktop and Open WebUI: prices, fertilizers, market options, soil and climate context, and `compute_recommendations`, which calls `POST /v1/recommendations/compute` here. The MCP server keeps no key table of its own: users create MCP keys in this API, and the MCP server asks this API whether a key is valid.

| Endpoint | Transport |
| :--- | :--- |
| `https://akilimo-mcp.akilimo.org/mcp` | Streamable HTTP (current MCP transport) |
| `https://akilimo-mcp.akilimo.org/sse` | SSE (older clients) |

The full client guide (other clients, the tool list, troubleshooting) is `docs/MCP_CONNECTION_GUIDE.md` in `akilimo-mcp-agent`.

## For users

1. Sign in to the playground (`/playground`) and open **API keys**.
2. Choose the type **MCP key**, give it a name and click **Generate**.
3. Copy the key: it is shown once. The banner also shows a ready Claude Desktop configuration:

   ```json
   {
     "mcpServers": {
       "akilimo-1.0": {
         "type": "http",
         "url": "https://akilimo-mcp.akilimo.org/mcp",
         "headers": { "Authorization": "Bearer ak_..." }
       }
     }
   }
   ```

4. Send it to the MCP server (not to this API) as `Authorization: Bearer <key>` or `X-API-Key: <key>`.

An MCP key has only the `mcp:use` ability, so it works with the MCP server and nowhere else in this API. Revoke or delete it in the same list; the MCP server caches answers for a minute, so it stops working within a minute.

What the MCP server answers:

| Answer | Meaning |
| :--- | :--- |
| `401` with `WWW-Authenticate: Bearer` | No key while the server requires one, or the key is unknown, revoked, expired or not an MCP key |
| `503` | The MCP server could not reach this API to check the key; try again later |
| `429` | Rate limit (per client IP: 120 requests a minute, 10 `compute_recommendations` calls a minute by default) |

## How the MCP server checks a key

```
MCP client --(Bearer ak_...)--> MCP server --POST /api/v1/mcp/verify-key {"key": "ak_..."}--> this API
                                            (X-Api-Key: the MCP server's own key)
                                <---------- {"valid": true | false} ---------------------------
```

- Abilities (`app/Auth/TokenAbility.php`): `mcp:use` for users' MCP keys (`MCP_ABILITIES` preset), `mcp:verify-keys` for the MCP server's own key.
- `POST /api/v1/mcp/verify-key` with `{"key": "ak_..."}` (`McpKeyController`, `ApiKeyService::verifyMcpKey`), protected by `auth.token:mcp:verify-keys`, throttled at 600 per minute. It answers `{"valid": true, "key_id", "key_prefix", "user": {"id", "username"}}` for an active, unexpired key with `mcp:use` (and records `last_used_at`), and `{"valid": false}` for anything else, without saying why.
- The MCP server caches each answer in memory by the key's SHA-256 (valid 60 s, invalid 30 s), so it does not call this API on every request. It only checks keys that start with `ak_`; other keys (its operators' `MCP_API_KEYS`) are checked from its own settings.

Keys are created by `ApiKeyService::issue` (`ak_` + 32 hex characters, SHA-256 stored, first 12 characters kept as `key_prefix`), the same format `AuthenticateWithToken` reads. Tests: `tests/Feature/Auth/McpKeyTest.php`.

## Setting up the MCP server (operators)

1. In this API's admin area (API keys), create a key with the ability **Verify MCP keys (MCP server only)** and nothing else.
2. Set it as the MCP server's `AKILIMO_API_KEY`. Without it the MCP server does not accept `ak_` keys and logs a warning at start.
3. Set `MCP_REQUIRE_KEY=true` on the MCP server if every client must send a key; otherwise its endpoints stay public and a key is only checked when one is sent.
4. Optional: `MCP_API_KEYS` on the MCP server holds operator keys (e.g. one per partner app) that work without this API.
