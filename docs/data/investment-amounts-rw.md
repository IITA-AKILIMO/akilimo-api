# Investment amounts: Rwanda

**IITA Internal Use** · Desk research, 2026-11-27 · **Seeded** as row 75 of `database/seeders/data/price_catalogue.csv` (item `investment_amount`, `per_acre`, RWF). An estimate: confirm it with the IITA Rwanda team.

## The gap

`GET /v1/investment-amounts/country/RW` returned nothing, because the `investment_amount` view is built from one catalogue row per country (BI, GH, NG and TZ had one, RW did not). The farmer could only type an amount.

## The row

| Country | Item | Price | Unit | Status | Confidence |
|---|---|---|---|---|---|
| RW | `investment_amount` | RWF 26,000 | per acre | active | low |

The list the app shows is that reference amount times 1, 2, 3, 4 and 6, plus the "exact amount" row: RWF 26,000, 52,000, 78,000, 104,000 and 156,000 per acre.

## Method

The reference is the cost of a modest first fertilizer dose per acre at the price a Rwandan farmer pays:

- **Dose:** about 25 kg of NPK 17:17:17 per acre (about 62 kg/ha). The Rwanda Agriculture Board lists 50 kg/ha of DAP for cassava, an older figure ([RAB chapter](https://agronomy.unl.edu/sites/unl.edu.ianr.agronomy-horticulture/files/media/file/OFRA%20Chapter%2013_Rwanda_HR.pdf)). No current cassava-specific NPK rate was found. The government rate for potato is 300 kg/ha ([CIP](https://cipotato.org/?p=100490)), so the top option (6 times, about 150 kg/acre) stays in that range.
- **Price:** NPK 17:17:17 (Tweze) retails at RWF 1,861/kg for 2026/27 and the government pays RWF 807, so the farmer pays about RWF 1,054/kg. Urea is RWF 1,980 retail with RWF 890 subsidy (about 1,090 to the farmer) and DAP RWF 1,950 with RWF 890 (about 1,060). 25 kg x 1,054 = RWF 26,350, rounded to 26,000 ([New Times](https://www.newtimes.co.rw/article/37366/news/agriculture/how-rwandas-fertiliser-subsidy-scheme-works), [AllAfrica, Jul 2026](https://allafrica.com/stories/202607030438.html)).
- **Sense check:** the other countries' reference amounts buy roughly 10 kg (NG) to 37 kg (TZ) of urea per acre at their catalogue prices, so 25 kg sits inside that range.

## Caveats

- The prices come from press reports on the subsidy scheme, with some inconsistent date stamps. Check them against the Ministry of Agriculture guidelines.
- The farmer price assumes the subsidy applies. At the unsubsidised price (about RWF 1,861/kg) the same 25 kg would cost about RWF 46,500.
- The Rwanda urea, DAP, MOP and NPK rows in the catalogue (RWF 27,900 to 35,650 per 50 kg bag) are far below the 2026/27 prices above (see `price-review-2026-11.md`). If those are corrected, revisit this amount.
- The row is `active` so the list exists on the day of the seed, like the other countries' backfilled amounts. Switch it to draft in the admin if the team wants to review it first. The seeder never changes a row that exists, so an edit made in the admin is kept.

## Running it

`php artisan db:seed --class=PriceCatalogueSeeder` adds the row to an existing database (it only adds what is missing); a new database gets it from `php artisan db:seed`.
