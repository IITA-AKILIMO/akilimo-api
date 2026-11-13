# Design: one price catalogue, with the API as source of truth

**IITA Internal Use** · Draft for team review, 2026-11-08 · Status: **phase 1 implemented**; open questions decided 2026-11-08 (see [Decisions](#decisions))

## Problem

AKILIMO prices live in three places that are maintained separately and drift apart:

| Where | What | Read by |
|---|---|---|
| API "choice list" tables: `fertilizer_prices`, `cassava_prices`, `maize_prices`, `potato_prices`, `operation_costs`, `investment_amount` | Hand-made rows of price options per country, e.g. TZ urea: 85,174 / 105,892 / 119,704 / 135,818, plus a `0` row ("I don't know") and a negative row ("Exact price"). `min_allowed_price`/`max_allowed_price` are the min and max of those rows | Mobile app picker. It reads fertilizer, cassava, maize and investment prices, but not potato prices or operation costs, and it never uses the allowed range (see [§5](#5-mobile-app-later-at-the-app-teams-pace)) |
| API `price_catalogue`, `starch_prices` | One value per country and item; starch prices by class | Recommendations engine, via `refresh_prices.R` → SQLite (`R/akilimo_db.R`); used e.g. to fill missing fertiliser costs (`R/fertilizers.R`) |
| Hardcoded in the engine (`R/AkilimoMain.R`) | Labour defaults (`default_lmo_costs`, NG and TZ only), maize 50/cob and 230/kg, sweet potato 120,000 and 384,000/t | Engine only |

Consequences seen in the November 2026 price review ([docs/data/price-review-2026-11.md](data/price-review-2026-11.md)):

- **Stale values.** Fertiliser defaults are 1.3–7× below market (NG urea ₦7,500 against ~₦32,500–50,500).
- **Stale price options.** In all five countries the options farmers pick from are far from current market prices. The allowed ranges are equally out of date, but nothing enforces them: the app ignores them, and its "Exact price" option accepts any value.
- **Silent disagreement.** Tanzania weeding was 60,000 in the seed file and the engine but 600,000 in a stale database row.
- **Missing metadata.** Units and bag sizes are wrong or missing (labour labelled `per_bag`; Burundi FOMI is a 25 kg bag), and nothing says whether a price is subsidised or open-market.

## Goal

One reviewed price per country and item, held in the API. The mobile app and the recommendations engine both read it, and every derived value (choice lists, allowed ranges, engine defaults) comes from it.

Non-goals: live market feeds; regional prices now (the schema leaves room for them, see D5); changing how recommendations are computed.

## Design

### 1. Price catalogue (API)

Extend `price_catalogue` rather than add a new table, so the engine's existing sync keeps working while it is migrated. One row per `(country, item)`:

| Column | Notes |
|---|---|
| `country`, `item` | Existing. `item` keys come from a fixed catalogue (below) |
| `region` | New, nullable, unused for now. Null means national. Leaves room for regional rows without a redesign (D5) |
| `price` | Existing. The **open-market** reference price clients pre-fill |
| `subsidised_price` | New, nullable. The programme farmer price where a subsidy exists (D1) |
| `unit` | Fixed to real units: `per_bag` + `bag_kg`, `per_tonne`, `per_kg`, `per_cob`, `per_acre` |
| `bag_kg` | New. 50 by default; **25 for Burundi FOMI**, priced per real 25 kg bag (D2) |
| `currency` | Existing, now filled (ISO code) |
| `min_price`, `max_price` | New. The allowed range clients validate against; covers both prices |
| `status` | New: `draft`, `pending_second_review` or `active`. Only `active` rows are served (D4) |
| `source`, `source_date`, `confidence` | New. Where the number comes from (e.g. "TFRA notice, Sep 2025", `high`) |
| `effective_from` | New. Lets a reviewed price be scheduled for a season |
| `updated_by` | New. The admin user who last changed it |

A `price_catalogue_history` table records every change (old value, new value, who, when) for audit.

Each country also gets a `default_price_basis` (`open_market` or `subsidised`), set by its team. It decides which price applies when a farmer hasn't said whether they get subsidised fertiliser (D1).

**Item catalogue** (keys as used today):
- fertilisers: `urea`, `DAP`, `NPK171717`, … matching `fertilizers.fertilizer_key` per country;
- `cassUP`, `maize_grain`, `maize_freshCob`, `sweetPotato_tuber`, `sweetPotato_flour`;
- labour: `manual_ploughing`, `manual_harrowing`, `manual_ridging`, `tractor_ploughing`, `tractor_harrowing`, `tractor_ridging`, `weeding1`, `weeding2`;
- `investment_amount`.

Starch prices keep their own tables, because they are priced by starch class; they already have an active flag per factory.

### 2. Read endpoint

`GET /v1/prices?country=TZ[&updated_since=ISO8601]`. It is public like the other reference data and returns only `active`, national (`region` null) rows whose `effective_from` has passed. Each row carries `price`, `subsidised_price`, `min_price`, `max_price`, `unit`, `bag_kg`, `currency`, `source_date` and `updated_at`. The response also includes the country's `default_price_basis`. The response has an `ETag`, and `updated_since` lets clients download only changes.

`GET /v1/price-catalogue` keeps its response shape until the engine has moved over, then becomes an alias. Since phase 1 it returns only `active` rows, so drafts never reach the engine. It does not apply the `region` or `effective_from` filters.

### 3. Choice lists generated from the catalogue

The mobile app's existing endpoints (`/v1/fertilizer-prices`, `/v1/cassava-prices`, `/v1/maize-prices`, `/v1/potato-prices`, `/v1/operation-costs`, `/v1/investment-amounts`) keep their response shape, but their rows are **generated** from the catalogue instead of hand-edited:

- the two marker rows, kept exactly as today, because the app decides what to show from `price_per_bag`:
  - **`price_per_bag = 0`:** "I don't know". The app sends price 0, and the engine then uses the catalogue price;
  - **`price_per_bag < 0`:** "Exact price". The app opens a box for the farmer's own price;
- four options at **−30 %, −10 %, +10 % and +30 %** of the applicable reference price (D3), rounded to the nearest 100 for NGN, TZS, RWF and BIF and to the nearest 5 for GHS. The app shows each option's `price_range` text as its label, so generate that too;
- `min_allowed_price` and `max_allowed_price` from the catalogue's `min_price` and `max_price`. The current app ignores them; they matter only if it starts validating.

The applicable reference price is the country's `default_price_basis`: the subsidised price where that is the basis and one exists, otherwise the open-market price.

Generation runs when a catalogue row is activated or changed, either as a job or a model observer that writes the existing tables. The app then needs **no change** in phase 1, and one price edit moves the choices and ranges with it. The step percentages and rounding live in config, so they can be tuned without code changes.

### 4. Recommendations engine

- `refresh_prices.R` reads `/v1/prices` (paginated, with `updated_since`) into the SQLite `price_catalogue` table, keeping unit, bag size, currency and range.
- The hardcoded defaults in `R/AkilimoMain.R` (labour, maize, sweet potato) become lookups in that table. A hardcoded value is used only if the table has no row, and that is logged at WARN.
- **Which price:** a new optional request field, `subsidisedFertilizer` (boolean), says whether the farmer gets subsidised fertiliser. If absent, the country's `default_price_basis` applies. Fertiliser costs the farmer typed always win over catalogue prices (D1).
- **Out-of-range prices are accepted and logged** (D6). The engine computes with the farmer's price, logs a WARN, and adds a note to the response naming the expected range. It never rejects or clamps the price.
- Existing behaviour is kept: refresh on start and on staleness (`*_MAX_AGE_DAYS`); the seed CSV only bootstraps a fresh install; a failed or empty refresh keeps the last good data.

### 5. Mobile app (later, at the app team's pace)

**How the app uses prices today** (`akilimo-mobile`, checked 2026-11-08):
- **Endpoints:** it calls `/v1/fertilizers`, `/v1/fertilizer-prices`, `/v1/cassava-prices`, `/v1/maize-prices`, `/v1/investment-amounts`, `/v1/starch-factories` and `/v1/cassava-units`, and posts requests to `/v1/recommendations/compute`. It does **not** call `/v1/price-catalogue`, `/v1/prices`, `/v1/potato-prices` or `/v1/operation-costs`.
- **Fertiliser price sheet** (`FertilizerScreen.kt`): one radio option per `fertilizer_prices` row. `0` means "I don't know", a negative value means "Exact price" (free text, any value), and anything else shows its `price_range`. `min_allowed_price`/`max_allowed_price` are stored but never used.
- **Labour costs** come only from what the farmer enters, or else the engine's catalogue and defaults.
- **Starch factories:** the app keeps only rows with `factory_active` true, so inactive (unconfirmed) factories stay hidden.

Optionally switch to `/v1/prices` directly: fetch when a country is selected, cache for offline use with `updated_at`, pre-fill the applicable price, and validate against the range. Until then the generated choice lists cover it.

Add one question to the fertiliser screen: **"Do you get subsidised fertiliser?"** (yes / no / not sure). Send it as `subsidisedFertilizer`, and leave it out for "not sure" so the country default applies (D1). Where a subsidised price exists, show both prices so the farmer recognises the one they pay.

### 6. Price update workflow

1. Research or field data is entered as `draft` rows. The review drafts in `docs/data/*.csv` can be imported this way.
2. The country team reviews the drafts in the admin, which shows each row's source and confidence.
3. A reviewer assigned to that country activates them, which also schedules them via `effective_from`. If a price moves more than **±30 %** from the current active price, activating it puts the row in `pending_second_review` instead. A second admin, not the first reviewer, must then confirm it (D4). New items with no current price need one reviewer.
4. Choice lists regenerate, and the app and engine pick up the change on their next sync.

## Rollout

| Phase | Scope | Who | Visible change |
|---|---|---|---|
| 1 ✅ | Catalogue columns, history, `GET /v1/prices`, admin screens for prices (draft, review, activate), per-country basis and reviewers, draft import. **Done** on branch `feat/price-review`, not yet merged into `develop` | API | None for clients; country teams can start entering prices |
| 2 | Engine syncs `/v1/prices`; hardcoded defaults become fallbacks | Engine | Engine uses reviewed prices |
| 3 | Choice lists generated from the catalogue, keeping the `0` and negative marker rows; the price options become current in all five countries | API | The app's price options follow the catalogue |
| 4 | App reads `/v1/prices` directly (optional) | Mobile | Fewer endpoints |

Each phase can ship and roll back on its own. Phase 3 replaces the hand-made option rows, so snapshot the existing tables first.

## Testing

- API feature tests: only active, effective rows are served; `updated_since`; history is written; choice lists are generated as expected.
- **Contract test:** a fixture of the `/v1/prices` response is checked in both repos. The engine's sync runs against it in CI, so a format change fails the build instead of quietly breaking prices.
- Engine: the existing regression suite (`tests/test_small.R`) must pass unchanged when phase 2 lands with the same values.

## Decisions

Decided 2026-11-08.

| # | Question | Decision |
|---|---|---|
| D1 | Subsidised or open-market price? | **Store both.** `price` is open-market and `subsidised_price` is optional. The farmer says whether they get subsidised fertiliser; if they don't say, the country's `default_price_basis` (set by its team) applies. Prices the farmer typed always win |
| D2 | Burundi FOMI bag size | **Per real 25 kg bag** (`bag_kg = 25`), matching what farmers buy. The engine already takes bag weight per fertiliser |
| D3 | App choice lists | **Generated:** −30 %, −10 %, +10 %, +30 % plus "exact price / don't know", rounded to 100 (NGN, TZS, RWF, BIF) or 5 (GHS). Values live in config |
| D4 | Approval | **One country reviewer, plus a second admin for changes over ±30 %** (`pending_second_review`) |
| D5 | Regional prices | **Later.** National only for now, with a nullable `region` column so regional rows can be added without a redesign |
| D6 | Engine and out-of-range request prices | **Accept and log.** Compute with the farmer's price, log a WARN, return a note with the expected range; never reject or clamp |

Country-team input is entered later through the admin UI, so none of it blocks phase 1. Phase 1 ships these as editable settings with safe starting values:
- **Each country's `default_price_basis`:** set on the country's admin page. It starts as `open_market` everywhere until a team changes it.
- **Prices:** country teams enter and update them as drafts in the admin price screens; activation follows D4. The review drafts in `docs/data/` are imported as the starting drafts.
- **Reviewers per country:** assigned in the admin user screens. Until someone is assigned, any admin can review.
- **The subsidy question's wording:** stored as a translation key served by `GET /v1/translations`, so it can be edited in the admin and translated without an app release.

> **Update (2026-11-11):** the price list responses no longer include min_local_price, max_local_price, min_allowed_price or max_allowed_price. Clients show label and send the choice's value; nothing enforced a range.
