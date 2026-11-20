# Breaking changes on `feat/price-catalogue-views`

Everything on this branch that can break a client, a script or direct database access, compared with
`develop`. Commits that are only additive (new fields, endpoints, admin screens) are not listed. Read this
before merging or deploying, and before telling the mobile app or partners.

## API responses and behaviour (mobile app, engine, partners)

| Change | Who is affected | What to do | Commit |
|---|---|---|---|
| The fertilizer, cassava, maize and sweet-potato price lists no longer return `min_local_price`, `max_local_price`, `min_allowed_price` or `max_allowed_price`. | Any client that reads them. The mobile app has its own branch for this (`fix/null-allowed-prices`). | Use `label`, `average_price` and `exact_price`. | `b8daf2e` |
| Price list row ids changed: a row is the catalogue id x 10 + the step, and the "I don't know" and "Exact price" rows use ids from 1,000,000. Ids stay the same when a price changes. | Anything that stored a picked option id. A farmer who had picked an option has to pick it again once. | Do not keep option ids across this release. | `d6a7c1a` |
| The maize grain list is priced per tonne, not per 50 kg. A client converts to the unit of sale as average x unit weight / 1000. | The mobile app's maize screen and any maize calculation. | Convert with the unit weight from `GET /v1/maize-units`. | `6265d1b` |
| Fertilizer choices show prices, not percentages, and the two marker rows read "I don't know" and "Exact price" instead of `0 TSh` and `-1 TSh`. | Clients that parsed the old text. | Use the `label` field. | `7bbe2d3`, `33d8927` |
| `GET /v1/fertilizers` (all and by country) lists only fertilizers with an **active price** in their country, and the fertilizer price list no longer has marker-only entries for an unpriced fertilizer. | Clients that expect every sold fertilizer to appear. | Activate the price in the Price catalogue (a second admin confirms a change over 30%). | `6d8ef57`, `429220f` |
| Creating or changing a price through the API needs a country that exists in `countries`, not just any two letters. An unknown country is now a validation error. | Writers of the price API. | Use a listed country code. | `ab0bfa0` |
| **The price catalogue API path is renamed**: `GET /api/v1/default-prices` is now `GET /api/v1/price-catalogue`, and the write endpoints `/api/v1/admin/default-prices` are now `/api/v1/admin/price-catalogue`. There is no alias: the old paths return 404. The response messages say "Catalogue price created/updated as a draft." | Any client that calls them (check the mobile app and any partner script). | Change the path. | this change |

**Farmer accounts (additive, but read before deploying):** `POST /api/v1/recommendations/compute` stays open and unchanged for
anonymous callers, but now passes through `farmer.optional`: a valid farmer token (and consent) saves the row with `user_id`, and
that farmer's result is cached apart from the shared cache and its `request_id` is the id of the saved row (anonymous callers still get a
fresh `request_id` per call). Any other credentials are ignored. New endpoints are under `/api/v1/account`
(see `docs/FARMER_ACCOUNTS_API.md`). Database: `users.password` may now be empty (a social-only account), `users.consent_at` and
`consent_version`, a new table `user_identities`, and `api_requests.user_id` (nullable, `ON DELETE SET NULL`); three migrations
`2026_11_12_000021` to `000023`. New dependency `laravel/socialite`; new config `config/farmer-accounts.php` and provider blocks in
`config/services.php`. Google sign-in is on by default (`FARMER_SOCIAL_PROVIDERS=google`) but is refused until `GOOGLE_CLIENT_ID` is set, and verification and reset
email stay off in production while `MAIL_MAILER` is `log`. Code that lists or exports `api_requests` must keep `user_id` out of any public export.

**Data, not code:** six Nigerian prices (urea, NPK 20-10-10, NPK 15-15-15, CAN, DAP, MOP) were replaced by published
figures and are **drafts** until a reviewer activates them, so those fertilizers are missing from the NG lists until
then (`f7a7425`). Two more drafts were added (NG NPK 20:12:26, BI dolomite) (`cd4e26a`). Each carries its source and
confidence; DAP and MOP are government procurement prices converted at an assumed NGN 1,400 per dollar.

## Database (direct SQL, reports, other services on the same database)

| Change | What to do | Commit |
|---|---|---|
| Table `default_prices` is now **`price_catalogue`**. The views that read it were rebuilt; their names and columns are unchanged. | Update any query, report or script that names the table. | `d557d23` |
| The history table `default_price_history` is now **`price_catalogue_history`**, and its `default_price_id` column is now **`price_catalogue_id`** (the foreign key to `price_catalogue` follows). | Update any query or report on the history. | this change |
| **Every database view starts with `vw_`**: `fertilizers`, `fertilizer_prices`, `cassava_prices`, `potato_prices`, `maize_prices`, `investment_amount`, `operation_costs` and `price_bands` are now `vw_fertilizers`, `vw_fertilizer_prices`, `vw_cassava_prices`, `vw_potato_prices`, `vw_maize_prices`, `vw_investment_amount`, `vw_operation_costs` and `vw_price_bands`. The columns, and so the API responses, are unchanged. The views are defined by the files in `database/views` and made by `php artisan views:rebuild`, which also runs after every migration (see `docs/VIEWS.md`). | Update any query, report or script that reads a view by its old name. | this change |
| The price lists (`fertilizer_prices`, `cassava_prices`, `potato_prices`, `maize_prices`, `investment_amount`, `operation_costs`) are **read-only views** over the catalogue, not tables. Writes fail. | Change the catalogue price instead. | `d6a7c1a` |
| `fertilizers` is a **read-only view** over `fertilizer_types` and `country_fertilizers`. | Write to those two tables (the admin does). | `23bbb60` |
| The tables `produce_prices` and `available_fertilizer` are dropped. | Nothing read them. | `d6a7c1a` |
| The seven `*_legacy` tables are dropped. They are copied into `legacy_table_archive` first, and a rollback restores them. | Drop `legacy_table_archive` in a later migration once the legacy data is no longer wanted. | `b373751` |
| `currencies.currency_code` is unique. | Do not insert a second row for one currency; a country names its currency in `countries.currency_code`. | `468fd1e` |

## Admin addresses

| Change | What to do | Commit |
|---|---|---|
| The admin pages moved: `/admin/default-prices` (and `/create`, `/batch-create`, `/batch-edit`, `/{id}/edit`, `/{id}/activate`, `/{id}/deactivate`, `/{id}/confirm`) are now under **`/admin/price-catalogue`**. There is no redirect from the old address. The route names changed from `admin.default-prices.*` to `admin.price-catalogue.*`. The menu entry and page titles say "Price catalogue". | Update bookmarks, links in documents, and any code that builds these URLs or route names. | this change |

## PHP code

| Change | What to do | Commit |
|---|---|---|
| `App\Models\DefaultPrice` is `PriceCatalogue`, `Models\Base\DefaultPrice` is `Base\PriceCatalogue`, `DefaultPriceRepo` is `PriceCatalogueRepo`, and `DefaultPriceHistory::defaultPrice()` is `priceCatalogue()`. | Update imports and calls. | `d557d23` |
| Everything else named for "default price" follows: `DefaultPriceHistory` (and its `Base` class) is `PriceCatalogueHistory`; the controllers, request and resources `DefaultPriceController` (admin and API), `DefaultPriceRequest`, `DefaultPriceResource` and `DefaultPriceResourceCollection` are `PriceCatalogue...`; `DefaultPricesSeeder` is `PriceCatalogueSeeder` and its data file `default_prices.csv` is `price_catalogue.csv`. The `User` relations are `priceCatalogueHistories()` and `priceCatalogueEntries()`. | Update imports, seeders that name the file, and any code that calls these. | this change |
| The admin front-end page folder `DefaultPrices/` is `PriceCatalogue/` (Inertia component names such as `PriceCatalogue/Index`), and the TypeScript types `DefaultPrice` and `DefaultPriceHistory` are `PriceCatalogue` and `PriceCatalogueHistory`. | Update any code that renders or imports them. | this change |
| The models of the views are named for them: `Fertilizer` is `VwFertilizer`, `FertilizerPrice` is `VwFertilizerPrice`, `CassavaPrice` is `VwCassavaPrice`, `PotatoPrice` is `VwPotatoPrice`, `MaizePrice` is `VwMaizePrice`, `InvestmentAmount` is `VwInvestmentAmount`, `OperationCost` is `VwOperationCost` and `PriceBand` is `VwPriceBand`, each in `App\Models` and `App\Models\Base`, and `BaseFertilizer` and the other aliases are `BaseVw...`. The repositories, resources and controllers keep their names and use the new models. | Update imports and calls that name a view model. | this change |

## Not changed, on purpose

The other public API paths (`/api/v1/prices`, `/api/v1/fertilizers`, ...), and the countries' `default_price_basis`
(the price basis a country uses by default, a different thing from the catalogue). Old migrations keep the names they
used when they ran, because a new database runs them before the rename.

Every migration on this branch has a `down()` and was rolled back and re-run on local data. Nothing here is deployed.

*IITA Internal Use*
