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
| `GET /v1/fertilizers` (all and by country) lists only fertilizers with an **active price** in their country, and the fertilizer price list no longer has marker-only entries for an unpriced fertilizer. | Clients that expect every sold fertilizer to appear. | Activate the price in Default prices (a second admin confirms a change over 30%). | `6d8ef57`, `429220f` |
| Creating or changing a price through the API needs a country that exists in `countries`, not just any two letters. An unknown country is now a validation error. | Writers of the price API. | Use a listed country code. | `ab0bfa0` |

**Data, not code:** six Nigerian prices (urea, NPK 20-10-10, NPK 15-15-15, CAN, DAP, MOP) were replaced by published
figures and are **drafts** until a reviewer activates them, so those fertilizers are missing from the NG lists until
then (`f7a7425`). Two more drafts were added (NG NPK 20:12:26, BI dolomite) (`cd4e26a`). Each carries its source and
confidence; DAP and MOP are government procurement prices converted at an assumed NGN 1,400 per dollar.

## Database (direct SQL, reports, other services on the same database)

| Change | What to do | Commit |
|---|---|---|
| Table `default_prices` is now **`price_catalogue`**. The views that read it were rebuilt; their names and columns are unchanged. | Update any query, report or script that names the table. | `d557d23` |
| The price lists (`fertilizer_prices`, `cassava_prices`, `potato_prices`, `maize_prices`, `investment_amount`, `operation_costs`) are **read-only views** over the catalogue, not tables. Writes fail. | Change the catalogue price instead. | `d6a7c1a` |
| `fertilizers` is a **read-only view** over `fertilizer_types` and `country_fertilizers`. | Write to those two tables (the admin does). | `23bbb60` |
| The tables `produce_prices` and `available_fertilizer` are dropped. | Nothing read them. | `d6a7c1a` |
| The seven `*_legacy` tables are dropped. They are copied into `legacy_table_archive` first, and a rollback restores them. | Drop `legacy_table_archive` in a later migration once the legacy data is no longer wanted. | `b373751` |
| `currencies.currency_code` is unique. | Do not insert a second row for one currency; a country names its currency in `countries.currency_code`. | `468fd1e` |

## PHP code

| Change | What to do | Commit |
|---|---|---|
| `App\Models\DefaultPrice` is `PriceCatalogue`, `Models\Base\DefaultPrice` is `Base\PriceCatalogue`, `DefaultPriceRepo` is `PriceCatalogueRepo`, and `DefaultPriceHistory::defaultPrice()` is `priceCatalogue()`. | Update imports and calls. | `d557d23` |

## Not changed, on purpose

The public API paths (`/api/v1/default-prices`, `/api/v1/prices`, ...), the admin routes, the controller and request
class names, and the `default_price_history` table keep their names.

Every migration on this branch has a `down()` and was rolled back and re-run on local data. Nothing here is deployed.

*IITA Internal Use*
