# Price tables: review and consolidation plan

**IITA Internal Use.** Reviewed 2026-11-10 against the owner's populated local database, after the choice lists became database views (migrations `2026_11_10_000001` to `000007`). Companion to [PRICE_CATALOGUE_DESIGN.md](PRICE_CATALOGUE_DESIGN.md).

## 1. What exists today

| Table | Rows | Role | Who writes it | Verdict |
|-------|-----:|------|---------------|---------|
| `default_prices` | 67 | **The catalogue.** One row per country and item: price, subsidised price, unit, bag size, currency, range, status, source, `fertilizer_key` | Admin (review workflow), seeders | Keep: the single source of truth |
| `default_price_history` | 1 | Audit trail of catalogue changes | `PriceCatalogueService` | Keep |
| `fertilizer_prices` | 180 | App fertiliser options | **View** over the catalogue | Done |
| `cassava_prices` | 25 | App cassava price bands | **View** over the `cassUP` price | Done |
| `potato_prices` | 5 | App sweet-potato price bands | **View** over `sweetPotato_tuber` | Done |
| `price_choice_steps`, `price_choice_rounding` | 4, 5 | Steps (−30/−10/+10/+30 %) and rounding for the views | Admin (SQL for now) | Keep: the one place the choice rules live |
| `maize_prices` | 13 | App maize bands (NG only: grain 6, cob 7) | Admin screens, partner API | **Still hand-made.** Unit mismatch with the catalogue (per kg, per cob) |
| `investment_amount` | 23 | Fixed investment amounts per area unit (TZ 57,550 … 345,300 per acre) | Admin screens | Hand-made; no catalogue price behind it |
| `operation_costs` | 60 | Labour min/max per operation and type, per country | Admin screens | Hand-made; overlaps the catalogue labour items |
| `starch_factories`, `starch_prices` | 12, 38 | Starch price per factory and class | Admin screens | Keep separate (priced by class); see 3.4 |
| `cassava_units`, `currencies` | 4, 119 | Unit weights, currency reference | Admin | Reference data, not prices |
| `fertilizers` | 38 | The fertiliser list per country, with `fertilizer_key` | Admin | Keep; now the key the catalogue links to |
| `cassava_prices_legacy`, `potato_prices_legacy`, `fertilizer_prices_legacy` | 25, 5, 203 | The hand-made and generated rows from before the views | Nothing | **Drop** after production has been checked |
| `produce_prices` | 40 | A second, generic range-list table (cassava, maize, potato per country) | **Nothing**: only a model, no route, screen or query | **Drop** (dead) |
| `available_fertilizer` | 13 | An old fertiliser list with a stray `price` column | **Nothing**: only a model | **Drop** (dead); superseded by `fertilizers` |
| `request_fertilizer` | 0 | Per-request fertiliser rows | Model only, empty | Check, then drop |

## 2. Findings

1. **There were three sources for the same prices.** The catalogue, the hand-made option tables, and the R engine's hardcoded defaults. Fertiliser, cassava and sweet potato now come from the catalogue only.
2. **Four tables exist that nothing uses** (`produce_prices`, `available_fertilizer`, `request_fertilizer`, and the three `_legacy` copies once production is checked). They add 6 tables and about 285 rows of noise.
3. **The three range lists share one shape** (country, min, max, two edge flags, sort order) and, as views, one piece of SQL repeated three times, differing only in the catalogue item.
4. **Three lists are still hand-edited**: maize, investment amounts, operation costs. Each needs a mapping decision from the owner (section 3).
5. **Currency is stored twice**: on every catalogue row and in `currencies` (119 rows, per country). The catalogue column is what the views and engine use.
6. **`min_usd` and `max_usd` are always 0** in the range lists and unused by the app (`CassavaPrices.kt`, `MaizePrices.kt` read only the local min and max).

## 3. Target model

```
default_prices  ──(reviewed prices: fertiliser, crops, labour, investment)──►  source of truth
price_choice_steps / price_choice_rounding  ──(rules)──►  views the app reads
        fertilizer_prices   cassava_prices   potato_prices   maize_prices   investment_amount   operation_costs
```

One catalogue, one set of rules, and the app's lists as views. Nobody writes to a list; they edit a catalogue price.

### 3.1 Drop what nothing uses (no decision needed)
Drop `produce_prices`, `available_fertilizer` and `request_fertilizer` (after confirming `request_fertilizer` is unused in production), and the three `_legacy` tables one release after the views have been seen working in production. Remove the dead models with them.

### 3.2 One range-list view instead of three
Create `price_bands` (item, country, step, min, max) once, and make `cassava_prices`, `potato_prices` and `maize_prices` thin views over it that filter on the item. A change to the band rule is then one migration, not three.

### 3.3 The three lists that were hand-made (done 2026-11-10, with assumptions to confirm)

Moved to views (migrations `…000013` to `…000022`) with the rules below. The rules were chosen from the data because no decision had been made; the owner should confirm them.

Original questions:
- **Maize.** Which unit do the existing bands use (per kg, per 100 kg bag, per tonne)? The catalogue has `maize_grain` per kg (NG 230) and `maize_freshCob` per cob (NG 50). Once the unit is fixed, maize becomes the same band view with `produce_type` grain or cob.
- **Operation costs.** Map `tillage`, `ridging` and `weeding` (manual, tractor, herbicide) to the catalogue labour items (`manual_ploughing`, `tractor_ploughing`, `manual_ridging`, `tractor_ridging`, `weeding1`, `weeding2`). Then `operation_costs` is a view of min and max around each labour price. `weeding` with `herbicide` has no catalogue item yet.
- **Investment amounts.** Add a catalogue item `investment_amount` per country (the reference amount per acre) and derive the list as multiples of it. TZ today is 57,550 × 1, 2, 3, 4, 6, which suggests a rule of that kind; the owner confirms the multiples.

### 3.4 Starch
Starch is priced by factory and class, so it keeps its own tables. Two small alignments: take its currency from the catalogue convention (it already has a `currency` column), and give factories an `active` flag as they have. No further merge: forcing it into `default_prices` would need a factory and class dimension the catalogue does not have.

### 3.5 Columns
Drop the constant `min_usd`/`max_usd` from the range-list API responses once the mobile app confirms it does not read them (it does not, today). Keep the legacy columns on the views meanwhile so the response shape does not change.

### 3.6 Currency, unit and item are picked, not typed (done 2026-11-10)
The default price forms (create, edit, batch create, batch edit) offer lists, and the server accepts only what the lists hold:

- **Currency** from the `currencies` table (one entry per code); choosing a country fills in its currency.
- **Unit** from `price_units` (per bag, acre, tonne, kg, cob).
- **Item** from `price_items`, each with the unit it is priced in; choosing an item fills in the unit. An item is saved with the list's spelling (`cassup` becomes `cassUP`).

All three live in the database, so they are managed there: a new unit or item is a row in `price_units` or `price_items` (managed on the Price settings screens). The config lists (`unit_by_item`, `units`) are gone. `price_items` was seeded with the labour, crop and investment items, every item already in the catalogue, and every fertiliser. Rows that fit no list (the stray `string` test row) are left out and cannot be edited through the forms until fixed.

### 3.7 Fertilisers normalised (done 2026-11-10)
`fertilizers` repeated a fertiliser's type, label, name and bag weight for every country that sells it (UREA and DAP four times each). It is now two tables and a view:

- **`fertilizer_types`**: each fertiliser once (type code as the primary key, label, name, bag weight). 23 rows from the 38. The type code is a natural key: the fertiliser key, the admin form and the API already use it, and it cascades if renamed. A new fertiliser is a row here.
- **`country_fertilizers`**: which type is sold in which country and how the app uses it (use case, CIS, CIM, available, sort order), keyed by `fertilizer_type` (a foreign key to the type code). One row per type and country, enforced. Row ids are the old `fertilizers` ids, so ids held by apps and option lists stay valid.
- **`fertilizers`** is a view over both with the old name and columns, so the API, recommendation service and the price views are unchanged. `fertilizer_key` is computed (type, underscore, country), so it can no longer disagree with them. The old table is kept as `fertilizers_legacy`.
- Writes go through `FertilizerRepo` to the two tables. The admin form picks the fertiliser from a list (its old "Type" list, STRAIGHT/COMPOUND/ORGANIC, did not match the stored types), picks the country, and shows the computed key and bag weight read-only. The API write endpoints take the type code, country and the country's use; name, label, weight and key are no longer accepted. Changing a fertiliser's name or weight is one change that applies to every country.
- **`default_prices.fertilizer_type`** (a foreign key to `fertilizer_types.type`) replaces the stored `fertilizer_key`: with the row's country it gives the key, so the database refuses a catalogue price for a fertiliser that does not exist, follows a rename, and refuses to delete a type that still has prices. In the price form, choosing the item (urea) picks the fertiliser whose label matches.
- **Use case is a list too:** `fertilizer_use_cases` (FR, CIM, CIS, ALL), a foreign key from `country_fertilizers.use_case`, picked in the form. The labels (FR fertilizer recommendation, CIM cassava and maize intercrop, CIS cassava and sweet potato intercrop) are descriptions to confirm with the owner; edit them in the table.
- **New fertilizer form** (`/admin/fertilizer-types/create`): name and bag weight; the type code is worked out from the name (NPK 20:10:10 becomes NPK_20_10_10, with a number added if taken), the catalogue label defaults from the name, and the label is added to `price_items` (per bag) so a price can be set for it. The code cannot be changed afterwards.
- Verified: the view equals the old table on every column of all 38 rows, and the option lists, fertilisers and cassava list were identical before a full rollback of the 28 migrations and after re-running them.

### 3.8 Screens for the new tables (done 2026-11-10)
- **Price settings** (`/admin/reference`): one set of screens, driven by `App\Support\ReferenceTables`, for the small tables the price forms and views read: price units, price items, fertilizer use cases, fertilizer types (edit and delete; a new fertilizer goes through its own form), price choice steps, rounding, units, investment multiples and operation cost items. A row's key is read-only once it exists; a row still used elsewhere cannot be deleted (the screen says so); the settings tables carry a warning that a change moves the app's lists in every country at once. Adding a table is one entry in the registry.
- **Preview before saving:** the forms for the rule tables (steps, rounding, per-item units, investment multiples, operation cost items) have a "Preview the change" button. It makes the change inside a database transaction, reads the affected app lists before and after, and rolls the change back, so the preview uses the real view logic and nothing is saved. It shows how many values would change per list and each row's value now and after; editing a field makes the preview stale and clears it. Deleting a row shows the same preview in the confirmation dialog (what would be removed or changed), and Delete stays disabled while it loads or if the row is still used elsewhere.
- **Navigation:** Price settings has a submenu in the sidebar (fertilizer types, new fertilizer, and each settings table); the Fertilizers page has buttons for a new fertilizer, the fertilizer types and selling one in a country.
- **Paging and filtering:** every admin table pages on the server and filters through the shared filter bar. Price settings tables page, search their text and choice columns, and filter by each choice column (offering only values the table holds); App price lists pages, switches between lists as tabs, filters by country, searches the lists that have a name, and filters to price choices or the words-only rows; the Excluded traffic rules, which loaded every rule at once, now page too.
- **App price lists** (`/admin/price-lists`): a read-only view of what the app is given (fertilizer, cassava, maize, sweet potato, investment, operation costs), with a country filter and the average the app sends. It replaces the manual screens that were removed.

### 3.9 Choices show prices, not percentages
The steps (-30, -10, +10, +30 %) are a rule the admin sets; the app is given prices only. The fertiliser options carry an empty `desc`, as the hand-made ones did, so no description reaches the farmer, and no other list has a text field that could. A test checks that no list response contains a percentage.

### 3.10 Every choice carries the label a client shows (done 2026-11-10)
A client (the mobile app or any other) shows a label for each choice, then maps the farmer's pick back to its value and sends that to the API. So each row of every list the app reads has a `label`:

- an ordinary choice is labelled with its price or range and, where the response has the currency, its symbol (`54,000 TSh`, `7,200 – 9,600 ₦`);
- the two rows every list ends with are labelled in words, not as a price: a price of 0 is **"I don't know"** (the engine then uses the catalogue price) and a negative price is **"Exact price"**; the investment list's 0 row is **"Exact amount"**. These rows also carry the words in `description`, and the fertiliser `price_range` says the words instead of "0 TSh" and "-1 TSh".

The wording lives in `App\Support\PriceMarker`; the fertiliser marker text is also in the `fertilizer_prices` view. The fertiliser label rounding now reads `price_choice_rounding` (it still read config). The admin **App price lists** screen shows the label as its first column ("Shown to the farmer"), so an admin sees exactly what is offered, and the maize, sweet potato and operation-cost labels carry no currency symbol because their API responses do not either.

## 4. Order of work

1. **Done 2026-11-10.** Dropped `produce_prices` and `available_fertilizer` with their models and the `produce_prices` seeder (migrations `…000008`, `…000009`). `request_fertilizer` was left: it is empty locally but production use is unconfirmed.
2. **Done 2026-11-10.** `price_bands` base view, with `cassava_prices` and `potato_prices` as thin views over it (migrations `…000010` to `…000012`); output identical before and after.
3. **Done 2026-11-10** (assumptions in 3.3, confirm with the owner): maize, operation costs and investment amounts are views; their admin screens and write endpoints are removed.
4. Drop the `_legacy` tables one release after production has the views.
5. Remove `min_usd`/`max_usd` from responses once the app confirms.

Each step is its own migration, reversible, and has its own tests.
