# Architecture

## Overview

AKILIMO API is a Laravel 12 RESTful API that acts as an **orchestration layer** between mobile/web clients and an external computation service. It validates, enriches, and forwards farm data to the Akilimo Compute Service, then caches and logs the result.

---

## Request Flow

```
Client
  │
  ├─ POST /v1/recommendations/compute
  │
  ▼
AuthenticateWithToken middleware
  │  tries Bearer token → then X-Api-Key header
  │
  ▼
RecommendationController::computeRecommendations()
  │  validates via ComputeRequest (UserInfoRules + ComputeFieldRules + FertilizerRules)
  │
  ▼
RecommendationService::compute()
  │  1. generate cache key (sha256 of compute_request + fertilizer_list)
  │  2. cache hit → return cached result with fresh request_id
  │  3. cache miss → performComputation()
  │
  ▼
RecommendationService::performComputation()
  │  1. build UserInfoData + ComputeRequestData DTOs
  │  2. load available fertilizers from DB (FertilizerRepo)
  │  3. mapFertilizersToExternalFormat() → flat array of {label}available / BagWt / CostperBag
  │  4. assemble AkilimoComputeData DTO
  │  5. logRequest() → writes to api_requests
  │
  ▼
AkilimoComputeService::compute()
  │  HTTP POST to external Akilimo Compute API
  │  timeout / retries from config
  │
  ▼
External Akilimo Compute API
  │
  ▼
RecommendationService
  │  updateRequestLogResponse() → writes response + duration_ms to api_requests
  │  returns {request_id, status, version, data{rec_type, recommendation, ...}}
  │
  ▼
Client
```

---

## Layers

### Controllers (`app/Http/Controllers/Api/`)
Thin — validate input, call one service or repository, return a resource. No business logic.

`RecommendationController` is the only controller with a write path. All others are read-only reference data endpoints.

`AuthController` extends `Illuminate\Routing\Controller` directly (not the project base) because the project base declares `abstract index()`.

### Services (`app/Service/`)

| Service | Responsibility |
|---------|---------------|
| `RecommendationService` | Orchestrates caching, logging, fertilizer mapping, DTO assembly |
| `AkilimoComputeService` | HTTP client wrapper for the external compute API. Reads all config (base URL, endpoint, timeout, retries) from `config/akilimo-compute.php` |
| `AuthService` | Login (credential check + token mint), logout (token deletion) |
| `ApiKeyService` | Issues API keys (`ak_` + 32 hex, SHA-256 stored) and checks users' MCP keys for the AKILIMO MCP server (`verifyMcpKey`, see [MCP_KEYS.md](./MCP_KEYS.md)) |

### Repositories (`app/Repositories/`)
All repos extend `BaseRepo` which provides: `find`, `selectOne`, `selectByCondition`, `create`, `update`, `delete`, and `paginateWithSort` (with column allowlist for `order_by`).

Repos implement `Contracts/Repository`. Controllers must not query models directly.

### DTOs (`app/Data/`)
Spatie LaravelData classes. `AkilimoComputeData` is the main payload sent to the compute service (~80+ properties). Its `toArray()` merges the `$fertilizers` flat array at the root level so fertilizer keys land at the top of the Plumbr payload.

### Validation (`app/Http/Requests/`, `app/Rules/`)
`ComputeRequest::rules()` merges three rule sets: `UserInfoRules`, `ComputeFieldRules`, `FertilizerRules`.

---

## Authentication

Two mechanisms are accepted on every protected route, tried in order by `AuthenticateWithToken` middleware:

### 1. Bearer Token
- Header: `Authorization: Bearer <token>`
- Stored as SHA-256 hash in `personal_access_tokens`
- Issued by `POST /v1/auth/login`, revoked by `POST /v1/auth/logout`
- TTL controlled by `AUTH_TOKEN_TTL_DAYS` (default 30 days)

### 2. API Key
- Header: `X-Api-Key: ak_...`
- Stored as SHA-256 hash in `api_keys`; raw value shown once at creation
- `key_prefix` (12 plain chars) stored for display without exposing the full key
- Can be scoped to specific abilities, or left null (= wildcard `*`)
- Must be `is_active = true` and not past `expires_at`

### Abilities (`app/Auth/TokenAbility.php`)

| Constant | Value | Purpose |
|---|---|---|
| `RECOMMENDATIONS_COMPUTE` | `recommendations:compute` | Compute recommendations |
| `RECOMMENDATIONS_READ` | `recommendations:read` | Read recommendation history |
| `PRICES_WRITE` | `prices:write` | Submit price updates |
| `FEEDBACK_READ` / `FEEDBACK_WRITE` | `feedback:read` / `feedback:write` | Read / submit user feedback |
| `TRANSLATIONS_READ` | `translations:read` | Read translations |
| `MCP_USE` | `mcp:use` | A user's key for the AKILIMO MCP server (works nowhere else in this API) |
| `MCP_VERIFY_KEYS` | `mcp:verify-keys` | The MCP server's own key, for `POST /v1/mcp/verify-key` |
| `API_KEYS_MANAGE` | `api-keys:manage` | Manage own API keys |
| `READ` / `WRITE` | `read` / `write` | Broad legacy abilities that imply the granular read / write ones (`BROAD_GRANTS`) |
| `ADMIN`, `WILDCARD` | `admin`, `*` | All abilities |

Presets: `PLAYGROUND_ABILITIES`, `PARTNER_ABILITIES`, `MCP_ABILITIES` (`mcp:use` only).

Who may grant what:
- `POST /v1/auth/api-keys` (`Api\ApiKeyController`): a new key may only carry abilities the calling token or key has (`can()`); no abilities means wildcard and needs a wildcard or `admin` caller.
- Admin area, API keys (`Admin\ApiKeyController`, reachable by admins and partners): admins see and manage every key, for any user, with any abilities. Partners see and manage only their own keys (another user's key answers 404), can only name themselves as owner, and can only grant `PARTNER_ABILITIES` plus `mcp:use`: never `admin`, `*`, `mcp:verify-keys`, the broad legacy abilities or an empty (wildcard) list. Tests: `tests/Feature/Auth/ApiKeyScopeTest.php`.

Routes declare required abilities as middleware parameters:
```php
Route::middleware('auth.token:read')->get(...)
Route::middleware('auth.token:write')->post(...)
```

---

## Route Groups

| Group | Throttle | Auth | Endpoints |
|-------|----------|------|-----------|
| Health | none | none | `GET /health` |
| Auth | 10/min | mixed | `POST /v1/auth/login` (public), `/logout` (protected) |
| Public reference data | `read` (120/min) | none | currencies, fertilizers, prices, factories, units |
| Protected reads | 120/min | required | recommendations history, user-feedback list, translations |
| API key management | 30/min | required | `GET/POST/PATCH/DELETE /v1/auth/api-keys` |
| Compute | `compute` (30/min) | none | `POST /v1/recommendations/compute` |
| Protected mutations | 30/min | required | `POST /v1/user-feedback` |

---

## Database

MariaDB in production/dev. SQLite in-memory for tests.

### Key Tables

| Table | Purpose |
|-------|---------|
| `api_requests` | Logs every compute request. Columns: `request_id` (UUID), `device_token`, `droid_request` (JSON), `plumber_request` (JSON), `plumber_response` (JSON), `request_started_at`, `request_duration_ms` |
| `personal_access_tokens` | Hashed bearer tokens. Sanctum-compatible schema |
| `api_keys` | Hashed long-lived keys: `key_prefix`, `key_hash`, `abilities` (JSON), `is_active`, `expires_at` |
| `fertilizers` | Available fertilizers by country: `fertilizer_key`, `fertilizer_label`, `name`, `weight`, `country` |
| `fertilizer_price` | Prices per fertilizer per country |
| `maize_prices`, `cassava_prices`, `potato_prices` | Commodity prices by country |
| `user_feedback` | Farmer feedback including `use_case` and `user_type` |

### Views & Procedures

| Object | Purpose |
|--------|---------|
| `v_app_request_stats_view` | Aggregated request statistics |
| `exclusion_flag_evaluation_proc` | Stored procedure — evaluates recommendation exclusion flags |
| `process_rec_request_proc` | Stored procedure — processes recommendation request data |

---

## Fertilizer Data Flow

The fertilizer mapping is a key transformation step. Understanding it is essential before touching `RecommendationService`, `FertilizerData`, or `AkilimoComputeData`.

1. Load all `available=true` fertilizers for the request's `country_code` from DB.
2. For each DB fertilizer, check if its `fertilizer_key` appears in the client's `fertilizer_list`.
3. If matched, client-supplied `selected`, `weight`, and `price` override DB defaults.
4. Build a flat array: `{fertilizer_label}available`, `{fertilizer_label}BagWt`, `{fertilizer_label}CostperBag`.

Example output merged into the Plumbr payload:
```
ureaavailable = true
ureaBagWt     = 50
ureaCostperBag = 12000.0
MOPavailable  = false
MOPBagWt      = 50
MOPCostperBag = 0.0
```

**Adding a new fertilizer:** insert a row in `fertilizers` with the correct `fertilizer_label`. No PHP changes needed.

---

## Caching

Cache key = `sha256(compute_request + fertilizer_list)`. `user_info` (email, phone, device_token) is intentionally excluded — it does not affect the computation result and would prevent cache reuse across users with identical farm inputs.

Each HTTP call receives a fresh `request_id` even when the result is served from cache.

TTL is controlled by `CACHE_TTL` (e.g. `30m`, `1h`, `24h`). Invalid values fall back to 1 hour with a `Log::warning`.

---

## Infrastructure

**Docker** — Multi-stage build: Node 20 Alpine (frontend assets) → PHP 8.3-fpm Alpine. Supervisor manages PHP-FPM and the Laravel queue worker.

**docker-compose.yml** — Full stack: API, MariaDB 11.2.3, Redis 7.4.1, Dozzle (log viewer).

**CI/CD** (`.github/workflows/`):

| Workflow | Trigger | What it does |
|----------|---------|--------------|
| `unit-test.yml` | Every push | PHP 8.3, SQLite in-memory, full Pest suite |
| `quality-checks.yml` | `develop` branch | Pest with coverage + SonarQube gate |
| `docker-build.yml` | `main`/`develop` | Builds and pushes `iita/akilimo-api` image |
