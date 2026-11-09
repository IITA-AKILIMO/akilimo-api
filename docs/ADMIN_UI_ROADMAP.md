# Admin UI Roadmap

A management interface for users, lookup data (fertilizers, prices, factories, etc.), and operational monitoring.

---

## Overview

The admin UI is an **Inertia.js + React** SPA served by Laravel, authenticated via Laravel sessions (separate from the API token/key auth used by mobile clients). Vite and Tailwind CSS 4 are already configured — Inertia.js slots in with minimal added complexity.

**Why Inertia.js over a standalone SPA?**
- Reuses existing Laravel routing, validation, and session auth — no separate admin API needed.
- No CORS, no token refresh logic, no duplicated auth layer.
- Blade-level simplicity for rendering, React-level richness for interactions.

---

## Current gaps

| Gap | What's needed | Status |
|-----|--------------|--------|
| All lookup data endpoints are GET-only | Add `POST`, `PUT`, `DELETE` for each resource | ✅ Done |
| No user management endpoints | Add CRUD under `/v1/admin/users` or a dedicated web route | ✅ Done |
| No admin ability scope | Add `admin` ability to `TokenAbility`; gate write endpoints | ✅ Done |
| No web session auth | Add `POST /admin/login` + `auth:web` guard for Inertia routes | ✅ Done |

---

## Phase 1 — Backend API Write Endpoints ✅ Complete

All existing lookup controllers are read-only. Each needs full CRUD before the UI can manage data.

**Deliverables per resource (follow existing controller/repo/resource/request pattern):**

```
POST   /v1/admin/{resource}
PUT    /v1/admin/{resource}/{id}
DELETE /v1/admin/{resource}/{id}
```

**Resources to cover:**

| Resource | Key fields | Status |
|----------|-----------|--------|
| Fertilizers | `fertilizer_key`, `fertilizer_label`, `name`, `weight`, `country`, `available` | ✅ Done |
| Fertilizer prices | `fertilizer_key`, `country`, `min_price`, `max_price` | ✅ Done |
| Maize prices | `country`, `min_price`, `max_price` | ✅ Done |
| Cassava prices | `country`, `min_price`, `max_price` | ✅ Done |
| Potato prices | `country`, `min_price`, `max_price` | ✅ Done |
| Starch prices | `country`, price fields | ✅ Done |
| Default prices | `country`, price fields | ✅ Done |
| Investment amounts | `country`, `min`, `max` | ✅ Done |
| Operation costs | `country`, cost fields | ✅ Done |
| Starch factories | `country`, `name`, `location` | ✅ Done |
| Currencies | `code`, `name`, `symbol` | ✅ Done |
| Cassava units | `unit`, `conversion_factor` | ✅ Done |
| Translations | `locale`, `key`, `value` | ✅ Done |

**Also add:**
- ✅ `GET/POST/PUT/DELETE /v1/admin/users` — user management
- ✅ `POST/DELETE /v1/admin/users/{id}/api-keys` — manage any user's API keys (admin only)

Gate all `/v1/admin/*` routes with a new `auth.token:admin` middleware check. ✅ Done

---

## Phase 2 — Web Session Auth & Inertia Scaffold ✅ Complete

Install and wire Inertia.js alongside the existing API — they share the app but use different route groups.

**Steps:**
- ✅ `composer require inertiajs/inertia-laravel` (v3.0 installed)
- ✅ `pnpm add @inertiajs/react react react-dom` (@inertiajs/react v3.0.3 installed)
- ✅ Add `HandleInertiaRequests` middleware to the `web` middleware group
- ✅ Create `routes/web.php` with an `admin` prefix group using `auth:web`
- ✅ Create `resources/js/app.tsx` as the Inertia entry point
- ✅ Set up a shared layout: sidebar, breadcrumbs, flash notifications (implemented in `AdminLayout.tsx`)
- ✅ Add `POST /admin/login` and `POST /admin/logout` web routes
- ✅ Seed a default admin web session user (`AdminUserSeeder` — configurable via env vars, generates API key)

**Route structure:**
```
GET  /admin/login          ✅
POST /admin/login          ✅
POST /admin/logout         ✅

GET  /admin                → Dashboard  ✅
GET  /admin/users          ✅
GET  /admin/fertilizers    ✅
GET  /admin/fertilizer-prices  ✅
GET  /admin/maize-prices   ✅
... etc                    ✅ all present
```

---

## Phase 3 — Core UI Components ⚠️ Partial

Build the shared building blocks all modules will use.

| Component | Description | Status |
|-----------|-------------|--------|
| `DataTable` | Paginated, sortable, filterable table — wired to Inertia's `useForm` for filter state | ✅ Done (`resources/js/components/DataTable.tsx`) |
| `ResourceForm` | Generic create/edit form with server-side validation error display | ✅ Done (`resources/js/components/ResourceForm.tsx`) |
| `CountryFilter` | Dropdown to filter all country-scoped lookup tables | ✅ Done (`resources/js/components/CountryFilter.tsx`) |
| `ConfirmDialog` | Reusable delete confirmation modal | ✅ Done (`resources/js/components/ConfirmDialog.tsx`) |
| `FlashBanner` | Success/error flash message from Inertia's shared props | ⚠️ Partial — implemented inline inside `AdminLayout.tsx`, not a standalone component |
| `Sidebar` | Nav with links to all resource sections | ⚠️ Partial — implemented as `Nav()` inside `AdminLayout.tsx` with collapsible groups, not a standalone component |

**Additional components built (not in original plan):**
- ✅ `Badge.tsx`
- ✅ `FormField.tsx`
- ✅ `Pagination.tsx`
- ✅ `CountrySelect.tsx`

---

## Phase 4 — User Management Module ✅ Complete

**Screens:**
- ✅ **User list** — username, email, created date, active status; searchable
- ✅ **Create/edit user** — name, username, email, password (hashed), admin flag
- ✅ **User detail** — shows the user's active API keys; admin can revoke or delete them
- ✅ **Deactivate / delete** — soft-disable vs hard delete

**API calls:** `GET/POST/PUT/DELETE /v1/admin/users` ✅

---

## Phase 5 — Lookup Data Modules ✅ Complete

Each module follows an identical pattern: **list → create → edit → delete**. Use the `ResourceForm` and `DataTable` components from Phase 3.

### 5a — Fertilizers ✅
- ✅ **Fertilizer master list** — filterable by country; toggle `available` inline
- ✅ **Fertilizer prices** — list per fertilizer/country; inline edit of min/max price; batch create + batch edit

### 5b — Commodity Prices ✅
- ✅ Maize prices
- ✅ Cassava prices
- ✅ Potato prices
- ✅ Starch prices + default prices (both with batch create/edit)
- ✅ Default price review workflow — draft → activate, second review over ±30 %, history

### 5c — Supporting Data ✅
- ✅ **Investment amounts** — per country, min/max
- ✅ **Operation costs** — per country (batch create + batch edit)
- ✅ **Starch factories** — per country, name, location
- ✅ **Currencies** — code, name, symbol
- ✅ **Cassava units** — unit name, conversion factor

### 5d — Translations ✅
- ✅ Locale selector, key search, inline value edit
- ✅ Batch edit (unified spreadsheet view)
- ❌ CSV import/export for translators — not yet implemented (see Phase 7)

---

## Phase 6 — Monitoring & Observability ✅ Complete

Read-only screens backed by existing data.

| Screen | Data source | Status |
|--------|------------|--------|
| **Dashboard** | `v_app_request_stats_view` — request counts, success rates, top countries | ✅ Done |
| **Request log** | `api_requests` — paginated list; click to inspect `plumber_request` / `plumber_response` JSON | ✅ Done (index + detail view) |
| **User feedback** | `user_feedback` — filterable by `use_case`, `user_type`, country | ✅ Done |

---

## Phase 7 — Hardening & Polish ❌ Not started

| Item | Notes | Status |
|------|-------|--------|
| Role-based access | `admin` = full write; `editor` = write lookup data only; `viewer` = read-only | ❌ Not done — access is auth-only (no role column or permission scopes) |
| CSV import | Bulk upload for prices and translations (validated, transactional) | ❌ Not done — `league/csv` is installed but no import handlers exist |
| Audit log | Append-only `admin_audit_log` table — who changed what and when | ❌ Not done — no migration or model |
| Activity indicators | Highlight stale prices (e.g. not updated in >90 days) | ❌ Not done |
| CI: Vite build check | Add `npm run build` step to `unit-test.yml` to catch asset compilation failures | ❌ Not done |
| E2E tests | Playwright smoke tests for login, CRUD on one resource, logout | ❌ Not done |

---

## Suggested build order

```
Phase 1  Backend write endpoints + admin ability gate          ✅ Complete
Phase 2  Inertia scaffold + web session auth + login screen    ✅ Complete
Phase 3  Core UI components (DataTable, ResourceForm, layout)  ✅ Complete (flash/sidebar in layout)
Phase 4  User management                                       ✅ Complete
Phase 5a Fertilizer master + prices                            ✅ Complete
Phase 5b Commodity prices                                      ✅ Complete
Phase 5c Supporting data (investments, costs, factories, currencies, units)  ✅ Complete
Phase 5d Translations                                          ✅ Complete
Phase 6  Monitoring dashboard + request log                    ✅ Complete
Phase 7  Roles, CSV import, audit log, E2E tests               ❌ Not started
```

Phases 1–6 are complete. Phase 7 items are independent and can be tackled in any order.
