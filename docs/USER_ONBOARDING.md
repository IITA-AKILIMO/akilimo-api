# API User Onboarding

This guide covers everything needed to start making requests to the AKILIMO API.

---

## Base URL

```
https://<your-host>/
```

All API endpoints are under `/v1/`. All responses are `application/json`.

---

## Authentication

Most endpoints require authentication. Two methods are supported — use either on any protected request.

### Option A: Bearer Token (short-lived)

1. Obtain a token by logging in:

```http
POST /v1/auth/login
Content-Type: application/json

{
  "username": "your_username",
  "password": "your_password"
}
```

Response:
```json
{
  "token_type": "Bearer",
  "token": "abc123...",
  "expires_at": "2026-05-08T00:00:00+00:00",
  "user": { "id": 1, "name": "...", "username": "..." }
}
```

2. Include the token on every subsequent request:

```http
Authorization: Bearer abc123...
```

3. Tokens expire after 30 days by default. Logout to invalidate immediately:

```http
POST /v1/auth/logout
Authorization: Bearer abc123...
```

---

### Option B: API Key (long-lived)

API keys do not expire unless you set an expiry date. They are better suited for server-to-server integration.

1. Generate a key (requires an active Bearer token):

```http
POST /v1/auth/api-keys
Authorization: Bearer abc123...
Content-Type: application/json

{
  "name": "My Integration Key",
  "abilities": ["read", "write"],
  "expires_at": "2027-01-01T00:00:00Z"
}
```

Response includes the key **once** — store it securely, it cannot be retrieved again:
```json
{
  "data": {
    "id": 1,
    "name": "My Integration Key",
    "key_prefix": "ak_a1b2c3d4",
    "abilities": ["read", "write"],
    "expires_at": "2027-01-01T00:00:00+00:00",
    "key": "ak_a1b2c3d4e5f6..."
  },
  "message": "API key created. Store it securely — it will not be shown again."
}
```

2. Include the key on every request:

```http
X-Api-Key: ak_a1b2c3d4e5f6...
```

---

### Token Abilities

| Ability | Required for |
|---------|-------------|
| `read` | GET protected endpoints (recommendations history, feedback, translations) |
| `write` | POST/compute endpoints (compute recommendations, submit feedback) |
| `api-keys:manage` | Create, list, revoke, delete own API keys. A new key can only have abilities the creating token or key has itself (otherwise `422`); leaving `abilities` out asks for a wildcard key, which only a wildcard or admin token may create |
| `mcp:use` | The AKILIMO MCP server only (see below); such a key works nowhere else in this API |

---

### MCP keys (AKILIMO tools in Claude Desktop and other AI clients)

The AKILIMO MCP server (`https://akilimo-mcp.akilimo.org/mcp`, repository `akilimo-mcp-agent`) gives AI clients the same data and recommendations as tools. When it requires a key, create one in the playground: sign in at `/playground` > **API keys** > type **MCP key** > **Generate**, and copy it (shown once, with a ready Claude Desktop configuration). Send it to the MCP server, not to this API, as `Authorization: Bearer <key>` or `X-API-Key: <key>`. Revoking or deleting it in the playground stops it within a minute. Details: [MCP_KEYS.md](./MCP_KEYS.md).

---

## Computing Recommendations

```http
POST /v1/recommendations/compute
Authorization: Bearer <token>   (or X-Api-Key)
Content-Type: application/json
```

**Minimal request body:**
```json
{
  "user_info": {
    "device_token": "device-abc123",
    "device_language": "en"
  },
  "compute_request": {
    "farm_information": {
      "country_code": "NG",
      "field_size": 1.5,
      "area_unit": "HECTARES"
    },
    "fertilizer_rec": true,
    "inter_crop_rec": false,
    "planting_practices_rec": false,
    "scheduled_planting_rec": false,
    "scheduled_harvest_rec": false
  },
  "fertilizer_list": [
    {
      "key": "urea",
      "name": "Urea 46%",
      "fertilizer_type": "STRAIGHT",
      "weight": 50,
      "price": 12000,
      "selected": true
    }
  ]
}
```

**Response:**
```json
{
  "request_id": "550e8400-e29b-41d4-a716-446655440000",
  "status": "success",
  "version": "1.0",
  "data": {
    "rec_type": "FR",
    "recommendation": { ... },
    "fertilizer_rates": { ... }
  },
  "report": {
    "url": "https://api.akilimo.org/api/v1/recommendations/reports/<id>/fertilizer_advice.pdf?expires=...&signature=...",
    "expires_at": "2026-11-12T10:00:00+00:00",
    "files": [{ "name": "fertilizer_advice.pdf", "url": "..." }]
  }
}
```

`request_id` is a unique UUID per call — use it when reporting issues.

`report` is the PDF report of the recommendation, or `null` when none could be made. Its `url` needs no key, cannot be guessed and stops working at `expires_at` (7 days by default). The report itself is kept with the recommendation history: a key with the `reports:read` ability gets new links at any time with `GET /v1/recommendations/reports/{report.id}`. The report shows the farmer's name and farm, so share the link only with them. A request served from the cache gets a report only when it carries the same personal details as the one that made it.

---

## Reference Data Endpoints

These endpoints are public — no authentication required.

| Endpoint | Description |
|----------|-------------|
| `GET /v1/currencies` | List of supported currencies |
| `GET /v1/fertilizers` | All available fertilizers |
| `GET /v1/fertilizers/country/{code}` | Fertilizers for a specific country (e.g. `NG`, `TZ`) |
| `GET /v1/fertilizer-prices` | Fertilizer price list |
| `GET /v1/fertilizer-prices/country/{code}` | Prices filtered by country |
| `GET /v1/maize-prices` | Maize commodity prices |
| `GET /v1/maize-prices/country/{code}` | Maize prices for a country |
| `GET /v1/cassava-prices` | Cassava commodity prices |
| `GET /v1/cassava-prices/country/{code}` | Cassava prices for a country |
| `GET /v1/potato-prices` | Potato commodity prices |
| `GET /v1/potato-prices/country/{code}` | Potato prices for a country |
| `GET /v1/investment-amounts` | Investment amount options |
| `GET /v1/investment-amounts/country/{code}` | Investment amounts for a country |
| `GET /v1/operation-costs` | Operation cost options |
| `GET /v1/operation-costs/country/{code}` | Operation costs for a country |
| `GET /v1/starch-factories` | Cassava starch factories |
| `GET /v1/starch-factories/country/{code}` | Factories for a country |
| `GET /v1/starch-prices` | Starch price list |
| `GET /v1/price-catalogue` | Default price reference data (active rows only) |
| `GET /v1/prices?country={code}` | Reviewed price catalogue with basis, ranges and ETag sync |
| `GET /v1/cassava-units` | Cassava unit definitions |

---

## Pagination

All list endpoints support pagination and sorting:

| Parameter | Default | Max | Description |
|-----------|---------|-----|-------------|
| `page` | 1 | — | Page number |
| `per_page` | 15 | 100 | Items per page |
| `sort` | `desc` | — | `asc` or `desc` |
| `order_by` | `id` | endpoint-specific | Column to sort by |

**Response envelope:**
```json
{
  "data": [ ... ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

---

## Managing API Keys

All key management endpoints require authentication.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/auth/api-keys` | List your API keys |
| `POST` | `/v1/auth/api-keys` | Create a new API key |
| `PATCH` | `/v1/auth/api-keys/{id}/revoke` | Disable a key (soft disable) |
| `DELETE` | `/v1/auth/api-keys/{id}` | Permanently delete a key |

---

## Health Check

```http
GET /health
```

Returns the status of all system components (database, compute service). No authentication required.

```json
{
  "status": "healthy",
  "checks": {
    "database": { "status": "UP" },
    "akilimo-compute": { "status": "UP", "url": "...", "http_status": 200 }
  }
}
```

---

## Error Responses

All errors follow a consistent JSON shape:

```json
{
  "message": "Human-readable description"
}
```

| Status | Meaning |
|--------|---------|
| `401` | Missing or invalid token / API key |
| `403` | Token lacks the required ability |
| `422` | Validation failed — check `errors` key for field-level details |
| `429` | Rate limit exceeded — retry after the `Retry-After` header value |
| `503` | Compute service unreachable |
