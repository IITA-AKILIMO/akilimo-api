# Design: one price catalogue, with the API as source of truth

**IITA Internal Use** · Draft for team review, 2026-11-08 · Status: **proposed, not started**

## Problem

AKILIMO prices live in three places that are maintained separately and drift apart:

| Where | What | Read by |
|---|---|---|
| API "choice list" tables: `fertilizer_prices`, `cassava_prices`, `maize_prices`, `potato_prices`, `operation_costs`, `investment_amount` | Hand-made rows of price options per country (e.g. TZ urea: 85,174 / 105,892 / 119,704 / 135,818 plus "exact price" and sentinel rows). The "allowed range" is the min and max of those rows | Mobile app (pickers, input checks) |
| API `default_prices`, `starch_prices` | One value per country and item; starch prices by class | Recommendations engine, via `refresh_prices.R` → SQLite (`R/akilimo_db.R`); used e.g. to fill missing fertiliser costs (`R/fertilizers.R`) |
| Hardcoded in the engine (`R/AkilimoMain.R`) | Labour defaults (`default_lmo_costs`, NG and TZ only), maize 50/cob and 230/kg, sweet potato 120,000 and 384,000/t | Engine only |

Consequences seen in the November 2026 price review ([docs/data/price-review-2026-11.md](data/price-review-2026-11.md)):

- **Stale values.** Fertiliser defaults are 1.3–7× below market (NG urea ₦7,500 against ~₦32,500–50,500).
- **Ranges that reject real prices.** In all five countries the choice lists exclude current market prices, and in several the stored defaults fall outside their own ranges.
- **Silent disagreement.** Tanzania weeding was 60,000 in the seed file and the engine but 600,000 in a stale database row.
- **Missing metadata.** Units and bag sizes are wrong or missing (labour labelled `per_bag`; Burundi FOMI is a 25 kg bag), and nothing says whether a price is subsidised or open-market.

## Goal

One reviewed price per country and item, held in the API. The mobile app and the recommendations engine both read it, and every derived value (choice lists, allowed ranges, engine defaults) comes from it.

Non-goals: live market feeds; per-district prices (possible later via an optional `region` column); changing how recommendations are computed.

## Design

### 1. Price catalogue (API)

Extend `default_prices` rather than add a new table, so the engine's existing sync keeps working while it is migrated. One row per `(country, item)`:

| Column | Notes |
|---|---|
| `country`, `item` | Existing. `item` keys come from a fixed catalogue (below) |
| `price` | Existing. The reference price clients pre-fill |
| `unit` | Fixed to real units: `per_bag` + `bag_kg`, `per_tonne`, `per_kg`, `per_cob`, `per_acre` |
| `bag_kg` | New. 50 by default; 25 for Burundi FOMI |
| `currency` | Existing, now filled (ISO code) |
| `min_price`, `max_price` | New. The allowed range clients validate against |
| `price_basis` | New: `open_market` or `subsidised` |
| `status` | New: `draft` or `active`. Only `active` rows are served |
| `source`, `source_date`, `confidence` | New. Where the number comes from (e.g. "TFRA notice, Sep 2025", `high`) |
| `effective_from` | New. Lets a reviewed price be scheduled for a season |
| `updated_by` | New. The admin user who last changed it |

A `default_price_history` table records every change (old value, new value, who, when) for audit.

**Item catalogue** (keys as used today):
- fertilisers: `urea`, `DAP`, `NPK171717`, … matching `fertilizers.fertilizer_key` per country;
- `cassUP`, `maize_grain`, `maize_freshCob`, `sweetPotato_tuber`, `sweetPotato_flour`;
- labour: `manual_ploughing`, `manual_harrowing`, `manual_ridging`, `tractor_ploughing`, `tractor_harrowing`, `tractor_ridging`, `weeding1`, `weeding2`;
- `investment_amount`.

Starch prices keep their own tables, because they are priced by starch class; they already have an active flag per factory.

### 2. Read endpoint

`GET /v1/prices?country=TZ[&updated_since=ISO8601]`. It is public like the other reference data and returns only `active` rows whose `effective_from` has passed. Each row carries `price`, `min_price`, `max_price`, `unit`, `bag_kg`, `currency`, `price_basis`, `source_date` and `updated_at`. The response has an `ETag`, and `updated_since` lets clients download only changes.

`GET /v1/default-prices` stays as it is until the engine has moved over, then becomes an alias.

### 3. Choice lists generated from the catalogue

The mobile app's existing endpoints (`/v1/fertilizer-prices`, `/v1/cassava-prices`, `/v1/maize-prices`, `/v1/potato-prices`, `/v1/operation-costs`, `/v1/investment-amounts`) keep their response shape, but their rows are **generated** from the catalogue instead of hand-edited:

- an "exact price / don't know" row, as today;
- options at fixed steps around the reference price, e.g. −30 %, −10 %, +10 %, +30 %, rounded to a sensible unit per currency;
- `min_allowed_price` and `max_allowed_price` from the catalogue's `min_price` and `max_price`.

Generation runs when a catalogue row is activated or changed, either as a job or a model observer that writes the existing tables. The app then needs **no change** in phase 1, and one price edit moves the choices and ranges with it. The step percentages and rounding are configuration, to be agreed with the app team.

### 4. Recommendations engine

- `refresh_prices.R` reads `/v1/prices` (paginated, with `updated_since`) into the SQLite `default_prices` table, keeping unit, bag size, currency and range.
- The hardcoded defaults in `R/AkilimoMain.R` (labour, maize, sweet potato) become lookups in that table. A hardcoded value is used only if the table has no row, and that is logged at WARN.
- Optionally, the engine rejects or clamps request prices outside `min_price` and `max_price`, using the same range as the app.
- Existing behaviour is kept: refresh on start and on staleness (`*_MAX_AGE_DAYS`); the seed CSV only bootstraps a fresh install; a failed or empty refresh keeps the last good data.

### 5. Mobile app (later, at the app team's pace)

Optionally switch to `/v1/prices` directly: fetch when a country is selected, cache for offline use with `updated_at`, pre-fill `price`, and validate against the range. Until then the generated choice lists cover it.

### 6. Price update workflow

1. Research or field data is entered as `draft` rows. The review drafts in `docs/data/*.csv` can be imported this way.
2. The country team reviews the drafts in the admin, which shows each row's source and confidence.
3. A reviewer activates them, which also schedules them via `effective_from`.
4. Choice lists regenerate, and the app and engine pick up the change on their next sync.

## Rollout

| Phase | Scope | Who | Visible change |
|---|---|---|---|
| 1 | Catalogue columns, history, `GET /v1/prices`, admin review screens, draft import | API | None for clients |
| 2 | Engine syncs `/v1/prices`; hardcoded defaults become fallbacks | Engine | Engine uses reviewed prices |
| 3 | Choice lists generated from the catalogue; fixes the ranges in all five countries | API | App choices and ranges follow the catalogue |
| 4 | App reads `/v1/prices` directly (optional) | Mobile | Fewer endpoints |

Each phase can ship and roll back on its own. Phase 3 replaces the hand-made range rows, so snapshot the existing tables first.

## Testing

- API feature tests: only active, effective rows are served; `updated_since`; history is written; choice lists are generated as expected.
- **Contract test:** a fixture of the `/v1/prices` response is checked in both repos. The engine's sync runs against it in CI, so a format change fails the build instead of quietly breaking prices.
- Engine: the existing regression suite (`tests/test_small.R`) must pass unchanged when phase 2 lands with the same values.

## Open questions

1. **Subsidy basis.** For each country, should the default be the subsidised or the open-market price? Ghana's programme fertiliser is free in 2026, and Rwanda subsidises about 45%.
2. **Burundi bag size.** Store FOMI per 25 kg bag, or convert to 50 kg?
3. **Choice-list steps and rounding,** to agree with the mobile app team.
4. **Approval.** One reviewer per country, or a second sign-off for changes over a threshold, e.g. ±30%?
5. **Regional prices.** Needed soon (e.g. TFRA prices vary ~15% by region), or later?
