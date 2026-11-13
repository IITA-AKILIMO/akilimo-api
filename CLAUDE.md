# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AKILIMO API is a Laravel 12 RESTful API that computes and delivers agricultural recommendations (fertilizer, intercropping, planting schedules) based on detailed farm data. It acts as an orchestration layer between clients and an external **Akilimo Compute** service.

## Common Commands

```bash
# Development
composer dev           # Start dev server on port 8600
composer dev:all       # Start server + queue worker + Vite (concurrently)

# Testing
composer test          # Run tests via artisan
composer tests         # Run tests via Pest
composer pest:coverage # Run Pest with coverage report

# Code Quality
composer lint:fix-all  # Fix all code style issues (Pint/PSR-12)
composer lint:check    # Check code style without fixing

# Model & IDE Helpers
composer model:gen     # Regenerate models and IDE helpers from DB schema

# Run a single test
php artisan test --filter=TestClassName
./vendor/bin/pest tests/Feature/SomeTest.php
```

## Architecture

### Request Flow
```
Client → ComputeRequest (validation) → RecommendationController
       → RecommendationService (caching, logging)
       → AkilimoComputeService (HTTP client)
       → External Akilimo Compute API
```

### Key Layers

**Controllers** (`app/Http/Controllers/Api/`) — Thin; delegate to services/repositories. `RecommendationController` handles the core `POST /v1/recommendations/compute` endpoint plus paginated history. All other controllers are read-only data endpoints.

**Services** (`app/Service/`) — Core services:
- `RecommendationService` — orchestrates computation, handles caching (configurable TTL), logs all requests to `api_requests`, maps fertilizer data, transforms request/response.
- `AkilimoComputeService` — HTTP client for the external Akilimo Compute service. Configurable via `config/akilimo-compute.php` (`base_url`, `endpoint`, `timeout` defaults to 120s, `retries` defaults to 3, `logging`).
- `AuthService` — handles login (user lookup, password verification, token minting) and token revocation. Keep auth logic here; `AuthController` is thin.
- `FarmerAccountService` — optional farmer accounts: register, login, consent, email verification and password reset codes (hashed, in the cache), claiming a phone's earlier advice by `device_token`, account deletion. `SocialSignInService` — Google and other providers through Laravel Socialite (access-token flow). Both are used by `AccountController` and `AccountHistoryController`; see `docs/FARMER_ACCOUNTS_API.md`.

**Repositories** (`app/Repositories/`) — Data access via `BaseRepo` (abstract, handles pagination/sorting/filtering). All repos implement `Contracts/Repository`. Don't bypass repositories to query models directly from controllers.

**DTOs** (`app/Data/`) — Spatie LaravelData classes are the canonical data shape passed between layers. `AkilimoComputeData` is the main payload sent to the Akilimo Compute service. `ComputeRequestData` is the inbound request shape.

**Validation** (`app/Http/Requests/`, `app/Rules/`) — `ComputeRequest` merges rules from three rule classes: `UserInfoRules`, `ComputeFieldRules`, `FertilizerRules`.

### Routes

Routes are split into four groups in `routes/api.php`:

**Public — no auth required:**
- `GET /health` — comprehensive health check
- `POST /v1/auth/login` — obtain bearer token
- `POST /v1/account/register|login|google|social/{provider}|email/verify|password/forgot|password/reset` — optional farmer accounts (limiters `sensitive` and `auth`)
- Read-only reference data (`currencies`, `fertilizers`, `fertilizer-prices`, `maize-prices`, `cassava-prices`, `potato-prices`, `investment-amounts`, `operation-costs`, `starch-factories`, `starch-prices`, `default-prices`, `cassava-units`) — throttle 120/min

**Protected — requires `Authorization: Bearer <token>` or `X-Api-Key: <key>`:**
- `POST /v1/auth/logout`, `/v1/auth/api-keys` (CRUD) — throttle 30/min
- `POST /v1/account/consent|logout|email/resend`, `GET /v1/account/history[/{request_id}|/ids]`, `DELETE /v1/account/history[/{request_id}]`, `POST /v1/account/history/delete` (bulk soft delete), `DELETE /v1/account` — farmer token only (`auth.token` + `farmer`; history and deletion also `farmer.consented`); a staff token or API key gets 403
- `POST /v1/recommendations/compute` — open to everyone (a valid farmer token, through `farmer.optional`, links the saved row to the account); limiter `compute`, 30/min per IP; `POST /v1/user-feedback` — limiter `write`, 30/min. **All request limits are in `config/rate-limits.php`** (seven keys: `auth` 10, `sensitive` 5, `read` 120, `write` 30, `compute` 30, `admin-write` 60, `mcp-verify` 600, each set by `API_RATE_<KEY>_PER_MINUTE` in `.env`), registered as named limiters in `AppServiceProvider` and used as `throttle:<key>`; routes sharing a key share one counter per IP; never write a number in a route file
- `GET /v1/recommendations`, `GET /v1/user-feedback`, `GET /v1/translations` — throttle 120/min
- `POST /v1/mcp/verify-key` — ability `mcp:verify-keys` (the AKILIMO MCP server's own key), throttle 600/min; checks users' MCP keys (`ApiKeyService::verifyMcpKey`, see `docs/MCP_KEYS.md`)

To protect a new route use the `auth.token` middleware alias. Ability-scoped protection:
```php
Route::middleware('auth.token:read')->get(...);
Route::middleware('auth.token:read,write')->post(...);
```

### Models
Located in `app/Models/Base/` — auto-generated by Reliese from DB schema. Regenerate with `composer model:gen` after schema changes. Do not hand-edit these; apply changes via migrations then regenerate. Columns the generated models do not know yet (`users.consent_at`, `users.consent_version`, `api_requests.user_id`) are added in the child models `User` and `ApiRequest` (`mergeFillable`/`mergeCasts` in the constructor), so regenerating keeps them. `UserIdentity` (linked social sign-ins) is hand-written.

### Authentication

Two auth mechanisms are accepted on every protected route (tried in order by `AuthenticateWithToken` middleware):

1. **Bearer token** — `Authorization: Bearer <token>`. Short-lived; stored hashed in `personal_access_tokens`. Issued by `POST /v1/auth/login`, revoked by `POST /v1/auth/logout`. TTL controlled by `AUTH_TOKEN_TTL_DAYS` (default 30 days).

2. **API key** — `X-Api-Key: ak_...`. Long-lived; stored hashed in `api_keys`. Generated via `POST /v1/auth/api-keys`. Only the SHA-256 hash is persisted — the raw key is shown once at creation. Keys have scoped abilities (`read`, `write`, `api-keys:manage`, granular ones such as `recommendations:compute`, and `mcp:use` for users' MCP keys created in the playground) or wildcard (`null` = `*`). New keys are issued through `ApiKeyService::issue`.

3. **Farmer token** — a Bearer token named `farmer`, issued by `POST /v1/account/login|register|google|social/...` and stored like any other. It carries only `FARMER_ABILITIES` (`recommendations:compute`, `history:own`), whatever the user's role. Middleware: `farmer` (`RequireFarmerToken`) accepts only these tokens on account routes, `farmer.consented` (`RequireConsent`) blocks history and deletion until consent is recorded, `farmer.optional` (`OptionalFarmerAuth`) never rejects and only marks a valid farmer's request on open routes (`compute`). Configuration: `config/farmer-accounts.php` and the provider blocks in `config/services.php`.

**`TokenAbility` constants** (`app/Auth/TokenAbility.php`) — single source of truth for all ability names. Reference these constants everywhere instead of raw strings.

**Admin setup:**
```bash
php artisan db:seed --class=AdminUserSeeder   # first run — prints key + password
php artisan admin:regenerate-api-key          # rotate the admin key
php artisan admin:regenerate-api-key --force  # skip confirmation
```

### Database
MariaDB in production, dev and tests (the tests need database views and generated columns, so SQLite does not work; CI runs MariaDB 11.2). Key tables:
- `api_requests` — logs every computation request/response (JSON columns for `droid_request`, `plumber_request`, `plumber_response`)
- `personal_access_tokens` — hashed bearer tokens (Sanctum-compatible schema)
- `api_keys` — hashed long-lived API keys with `key_prefix` (plain, for display), `abilities` (JSON), `is_active`, `expires_at`
- `fertilizers`, `fertilizer_price`, `available_fertilizer`, `request_fertilizer`
- `maize_prices`, `cassava_prices`, `potato_prices`, `produce_price`
- `user_feedback` — includes `use_case` field
- Views: `v_app_request_stats_view`
- Stored procedures: `exclusion_flag_evaluation_proc`, `process_rec_request_proc`

### Fertilizer Data Flow

Understanding this is essential before touching `RecommendationService`, `FertilizerData`, or `AkilimoComputeData`.

**Database schema** (`fertilizers` table — key columns):

| column | example value | purpose |
|---|---|---|
| `fertilizer_key` | `urea` | Stable identifier; matched against `key` in the client's `fertilizer_list` |
| `fertilizer_label` | `urea` | Prefix used to build compute service field names (see below) |
| `name` | `Urea 46%` | Human-readable display name |
| `weight` | `50` | Default bag weight in kg |
| `country` | `NG` | Country the fertilizer is available in |

**Client request** — the `fertilizer_list` array each item carries:
```json
{
  "key":             "urea",
  "name":            "Urea 46%",
  "fertilizer_type": "STRAIGHT",
  "weight":          50,
  "price":           12000,
  "selected":        true
}
```

**Mapping step** — `RecommendationService::mapFertilizersToExternalFormat()`:

1. Loads all `available=true` fertilizers for the request's country from the DB.
2. For each DB fertilizer, checks whether its `fertilizer_key` appears in the client's `fertilizer_list`.
3. If matched, the client-supplied `selected`, `weight`, and `price` override the DB defaults.
4. Builds a flat array keyed by `{fertilizer_label}available`, `{fertilizer_label}BagWt`, `{fertilizer_label}CostperBag`.

**Resulting array** passed to `AkilimoComputeData` as `['fertilizers' => ...]`:
```php
[
    'ureaavailable'       => true,   // bool  — was it selected by the farmer?
    'ureaBagWt'           => 50,     // int   — kg per bag
    'ureaCostperBag'      => 12000.0,// float — local currency per bag

    'MOPavailable'        => false,
    'MOPBagWt'            => 50,
    'MOPCostperBag'       => 0.0,

    'DAPavailable'        => false,
    'DAPBagWt'            => 50,
    'DAPCostperBag'       => 0.0,

    // … one triplet per fertilizer row for that country
]
```

**`AkilimoComputeData::toArray()`** merges these keys directly into the top-level Akilimo Compute payload — they must not be nested under a `fertilizers` key in the HTTP body. The override in `toArray()` handles this:
```php
public function toArray(): array
{
    $data = parent::toArray();           // all other DTO fields
    $fertilizers = $data['fertilizers']; // the flat array above
    unset($data['fertilizers']);
    return array_merge($data, $fertilizers); // merged at root level
}
```

**Adding a new fertilizer type** — only two steps needed:
1. Insert a row into `fertilizers` with `fertilizer_label` set to the compute service field prefix (e.g. `NPK201010`).
2. Ensure the client sends a matching `key` in `fertilizer_list` if the farmer selects it.

No PHP code changes are required.

### Error Handling
`RecommendationException` is the domain exception — carries HTTP status code and body. `AkilimoComputeService` converts HTTP client failures into `RecommendationException`. The `JsonFormatResponse` middleware forces all responses to `application/json`.

## Infrastructure

**Docker:** Multi-stage build (Node 20 Alpine for frontend assets → PHP 8.3-fpm Alpine). Supervisor manages PHP-FPM and the Laravel queue worker. `docker-compose.yml` provides the full stack: API, MariaDB 11.2.3, Redis 7.4.1, Dozzle.

**CI/CD** (`.github/workflows/`):
- `unit-test.yml` — runs on every push and pull request (a push and the pull request of one branch replace each other), PHP 8.4, MariaDB 11.2 service. Steps: code style (`composer lint:check`), Pest through `composer run test:ci` (plain Pest, not `--bail`, so every failure shows) with a JUnit report, a step summary and an uploaded `test-results` artifact, and a non-blocking `composer audit`. No Node build: `tests/TestCase.php` calls `withoutVite()`. Setup lives in the reusable action `.github/actions/php-setup` (also used by `quality-checks.yml`); `.github/dependabot.yml` keeps the actions current
- `quality-checks.yml` — Pest with coverage + SonarQube gate (develop branch)
- `auto-approve.yml` — approves pull requests opened by bots (Dependabot, the release bot) only after the `UnitTests` workflow succeeded, from this repository only, with `hmarr/auto-approve-action` and a GitHub App token (`APP_CLIENT_ID` variable, `APP_PRIVATE_KEY` secret; `actions/create-github-app-token@v3.3.0` takes the App's client ID). The allowed bot logins come from the `ALLOWED_BOTS` environment variable of the workflow (a space-separated list of bare bot names, default `github-actions dependabot munywele-bot`; `name`, `name[bot]` and `app/name` all match in any letter case, and GitHub must also report the author as a bot; set a repository variable `ALLOWED_BOTS` to override the list without editing the file). Human pull requests are never auto-approved. It replaced `pull-request-automation.yml`, which approved every pull request not opened by masgeek the moment it opened, without waiting for any check
- `docker-build.yml` — builds and pushes `iita/akilimo-api` image (main/develop)

## Environment Configuration

Key `.env` variables beyond standard Laravel:
```
# External computation service
AKILIMO_COMPUTE_BASE_URL=      # Required — base URL of the Akilimo Compute service
AKILIMO_COMPUTE_ENDPOINT=      # Endpoint path (default: /compute)
AKILIMO_COMPUTE_TIMEOUT=       # HTTP timeout in seconds (default: 120)
AKILIMO_COMPUTE_RETRIES=       # Retry attempts on failure (default: 3)
AKILIMO_COMPUTE_LOGGING=       # Enable request/response logging (default: true)

# Authentication
AUTH_TOKEN_TTL_DAYS=30         # Bearer token lifetime in days

# Farmer accounts (config/farmer-accounts.php, config/services.php; see docs/FARMER_ACCOUNTS_API.md)
FARMER_SOCIAL_PROVIDERS=google # default google (refused until GOOGLE_CLIENT_ID is set); e.g. google,facebook; blank = social sign-in off
GOOGLE_CLIENT_ID=              # non-secret OAuth client IDs (comma separated); also the allowed token audiences
                               # <PROVIDER>_CLIENT_SECRET and <PROVIDER>_REDIRECT_URI exist for every provider (optional)
FARMER_CONSENT_VERSION=draft-1 # label stored with each consent; the final wording comes from the owner
# Verification and reset email go through MAIL_MAILER; in production they stay off while it is "log"
DEFAULT_ADMIN_PASSWORD=        # Required for AdminUserSeeder

# Cache
CACHE_STORE=redis              # Use redis; falls back to database
CACHE_TTL=1h                   # Recommendation cache TTL (e.g. 30m, 1h, 24h)

# Timezone
APP_TIMEZONE=UTC               # Affects ISO 8601 offset in all API date fields
```
