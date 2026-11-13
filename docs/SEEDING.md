# Seeding a new database

A new database is built with `php artisan migrate` and then `php artisan db:seed`. The migrations create the
schema and the rules and lists they own (units, price items, steps, rounding, maize units, currencies); the seeders
add everything else the app needs. Seeding is safe to run again: it adds what is missing and never changes a row
that exists, so a reviewed price, an edited crop or a switched-off factory is left as it is.

## What is seeded, and from where

| Data | Seeder | File in `database/seeders/data/` |
|---|---|---|
| Admin user (needs `DEFAULT_ADMIN_PASSWORD` in `.env`) | `AdminUserSeeder` | none |
| Countries (and which are active) | `CountriesSeeder` | none |
| Each country's currency | `CountryCurrenciesSeeder` | `country_currencies.csv` |
| Crops | `CropsSeeder` | `crops.csv` |
| Cassava units | `CassavaUnitsSeeder` | `cassava_units.csv` |
| Fertilizers (once each) and where each is sold | `FertilizerSeeder` | `fertilizer_types.csv`, `country_fertilizers.csv` |
| Traffic rules that mark staff and test requests | `TrafficExclusionSeeder` | `traffic_exclusions.csv` |
| Starch factories (with their ids) and prices | `StarchPricesSeeder` | `starch_factory_ids.csv`, `starch_prices.csv` |
| Translations | `TranslationsSeeder` | `translations.csv` |
| The price catalogue: reviewed prices, drafts with their sources, the investment amounts | `PriceCatalogueSeeder` | `price_catalogue.csv` |

The order matters: the fertilizers are seeded before the prices, so a fertilizer's price is linked to it, and the
country currencies after the countries.

## Ids are part of the data

The app and the price option lists refer to ids (a price option is the catalogue id x 10 + the step, a fertilizer is
its `country_fertilizers` id, a starch factory is its id), so the files carry them and the seeders insert them.
Two migrations insert rows before the seeders run (the inactive Ghana and Rwanda starch factories, and the November
labour-cost drafts); they use fixed ids (11 and 12, and 55 to 68) so the seeded rows around them get the same ids as
in an existing database.

## Where the data came from

The files were built on 2026-11-11 from a database dump taken on 2026-11-10 (before the price work), upgraded with
every migration on the `feat/price-catalogue-views` branch, and checked: a new database that is migrated and seeded
matches that upgraded database on every table and every price list, with two deliberate differences. Tanzanian
weeding is the corrected 60,000 and 45,000 (the dump still held ten times that; migration `000020` corrects a
database that has the old values), and Kenya is not switched on (it was active in the dump, which looks like an
admin's choice rather than seed data). `tests/Feature/SeedersTest.php` covers the counts, the ids and running twice.

## Not seeded

- `coverage_areas`: run `php artisan coverage:sync` (it reads the engine).
- The `authorities` table (four legacy account rows that nothing in the code reads), users other than the admin, API
  keys, and the request, report and feedback logs.
- The legacy price tables (they were archived and dropped).

*IITA Internal Use*
