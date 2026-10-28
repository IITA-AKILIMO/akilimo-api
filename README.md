# AKILIMO API

Laravel 12 RESTful API that computes and delivers agricultural recommendations (fertilizer application, intercropping, scheduled planting) based on detailed farm data. Acts as an orchestration layer between clients and an external computation service.

[![Tests](https://github.com/IITA-AKILIMO/new-akilimo-api/actions/workflows/unit-test.yml/badge.svg)](https://github.com/IITA-AKILIMO/new-akilimo-api/actions/workflows/unit-test.yml)
[![Build](https://github.com/IITA-AKILIMO/new-akilimo-api/actions/workflows/docker-build.yml/badge.svg)](https://github.com/IITA-AKILIMO/new-akilimo-api/actions/workflows/docker.yml)

---

## Documentation

| Guide | Audience |
|-------|----------|
| [Architecture](docs/ARCHITECTURE.md) | How the system is structured, request flow, auth, DB schema |
| [User Onboarding](docs/USER_ONBOARDING.md) | Integrating with the API — auth, endpoints, request/response shapes |
| [Developer Onboarding](docs/DEVELOPER_ONBOARDING.md) | Local setup, project structure, contributing, testing |
| [Supported area](docs/COVERAGE.md) | Where recommendations can be computed: GeoJSON ingest, map endpoints, early coordinate validation |

---

## Quick Start

### Requirements

| Dependency | Version |
|---|---|
| PHP | 8.3+ |
| Laravel | 12.x |
| MariaDB | 11.2+ |
| Redis | 7.4+ |

### Docker (recommended)

```bash
docker compose up -d
```

The API is available on port **8600**. MariaDB, Redis, and Dozzle (log viewer at `:8080`) start alongside it.

### Local setup

```bash
git clone https://github.com/IITA-AKILIMO/new-akilimo-api.git
cd new-akilimo-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed          # seeds reference data + admin user
composer dev                 # start dev server on port 8600
```

See [Developer Onboarding](docs/DEVELOPER_ONBOARDING.md) for the full environment variable reference and all available commands.

---

## Authentication

Two methods are supported interchangeably on all protected routes:

- **Bearer token** — `Authorization: Bearer <token>` — obtained via `POST /v1/auth/login`, valid for `AUTH_TOKEN_TTL_DAYS` days.
- **API key** — `X-Api-Key: ak_...` — generated via `POST /v1/auth/api-keys`, long-lived, scoped to specific abilities.

See [User Onboarding](docs/USER_ONBOARDING.md) for step-by-step auth setup.

---

## API Routes

### Public (no auth)

| Method | Path | Description |
|---|---|---|
| `GET` | `/health` | Service health check |
| `POST` | `/v1/auth/login` | Obtain bearer token |
| `GET` | `/v1/currencies` | Currency reference data |
| `GET` | `/v1/fertilizers` | Fertilizer catalogue |
| `GET` | `/v1/fertilizer-prices` | Fertilizer prices |
| `GET` | `/v1/investment-amounts` | Investment amount options |
| `GET` | `/v1/operation-costs` | Operation cost options |
| `GET` | `/v1/starch-factories` | Starch factory list |
| `GET` | `/v1/starch-prices` | Starch prices |
| `GET` | `/v1/default-prices` | Default produce prices |
| `GET` | `/v1/cassava-units` | Cassava unit options |
| `GET` | `/v1/cassava-prices` | Cassava prices |
| `GET` | `/v1/potato-prices` | Potato prices |
| `GET` | `/v1/maize-prices` | Maize prices |

### Protected (requires Bearer token or API key)

| Method | Path | Description |
|---|---|---|
| `POST` | `/v1/auth/logout` | Revoke current token |
| `GET` | `/v1/auth/api-keys` | List own API keys |
| `POST` | `/v1/auth/api-keys` | Generate API key |
| `PATCH` | `/v1/auth/api-keys/{id}/revoke` | Revoke API key |
| `DELETE` | `/v1/auth/api-keys/{id}` | Delete API key |
| `POST` | `/v1/recommendations/compute` | Compute recommendation |
| `GET` | `/v1/recommendations` | Request history |
| `GET` | `/v1/user-feedback` | List feedback |
| `POST` | `/v1/user-feedback` | Submit feedback |
| `GET` | `/v1/translations` | Translation strings |

---

## Code Quality & CI

- **Tests:** Pest — `composer tests`. 120 tests, 255 assertions.
- **Coverage:** `composer pest:coverage` — HTML report + SonarQube gate on `develop`.
- **Linting:** Laravel Pint (PSR-12) — `composer lint:fix-all` before opening a PR.
- **CI:** GitHub Actions runs tests on every push and builds the Docker image on `main`/`develop`.
