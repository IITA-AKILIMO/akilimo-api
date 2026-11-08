# Land preparation and weeding costs: Rwanda, Ghana, Burundi

**IITA Internal Use** · Draft reference, researched 2026-11-08 · **Not seeded**: nothing here is loaded into the database or the compute service.

Post-planting (PP) advice is enabled for all five countries (`REC_TYPE_COUNTRIES` in the compute service's `R/AkilimoMain.R`), but default operation costs exist only for Nigeria and Tanzania. For RW, GH and BI the user has to type every cost. This note proposes *estimated* defaults. Confirm them with the IITA country teams before they are used.

Draft rows: [`labour_costs_candidates.csv`](labour_costs_candidates.csv) (default-price format plus basis, confidence and source columns).

## Units

The compute service treats these costs as **per acre, in local currency**; it converts them to per hectare (`default_lmo_costs` in `R/AkilimoMain.R`). The API's `default_prices` rows for these items are labelled `per_bag`, which is wrong for labour. See the open issues below.

## Method

No source publishes per-operation rates for these countries, apart from tractor ploughing in Ghana. Manual operations are therefore estimated as **rural daily wage × person-days per acre**, using the same person-days in every country:

| Operation | Person-days / acre | Per hectare |
|---|---|---|
| Ploughing (manual) | 10 | ~25 |
| Ridging (manual) | 8 | ~20 |
| First weeding | 8 | ~20 |
| Second weeding | 6 | ~15 |

Land preparation (ploughing + ridging, ~45 person-days/ha) sits between the FAO Tanzania figure of 30 ([FAO](https://www.fao.org/4/a0154e/A0154E09.htm)) and about 50 in a Uganda/Kenya field study ([HAL](https://hal.science/hal-00886530/document)). Weeding (~35 person-days/ha for two weedings) is above FAO's 21 and well below that study's season-long total, which covers more than two weedings. These are assumptions; they are the main uncertainty in every estimate below.

## Estimates (per acre)

| Item | RW (RWF) | GH (GHS) | BI (BIF) |
|---|---|---|---|
| Daily wage used | 2,000 | 55 | 4,300 |
| manual_ploughing | 20,000 | 550 | 43,000 |
| manual_ridging | 16,000 | 440 | 34,000 |
| weeding1 | 16,000 | 440 | 34,000 |
| weeding2 | 12,000 | 330 | 26,000 |
| tractor_ploughing | none | **250** (reported) | none |
| tractor_ridging | none | 250 (assumed) | none |

### Rwanda
- **Wage, about 2,000 RWF/day:** a press estimate of typical farm-labourer pay ([IGIHE](https://en.igihe.com/social/article/inside-rwandas-pay-scale-who-earns-what-across-sectors)). This is consistent with the NISR 2025 Labour Force Survey's 408 RWF/hour in agriculture over a five-hour day ([NISR](https://statistics.gov.rw/sites/default/files/documents/2026-03/Annual%20LFS%202025%20Report..pdf)).
- **Tractors:** no tractor-hire rates found. Cassava land is mostly hand-prepared on hillsides, so tractor items are left empty.

### Ghana
- **Wage, about 55 GHS/day:** an assumption. The legal floor is GHS 21.77/day from January 2026 ([WageIndicator, 2025](https://wageindicator.org/salary/minimum-wage/minimum-wages-news/2025/minimum-wage-updated-in-ghana-from-1-march-2025-march-17-2025), [Employsome](https://employsome.com/hire/ghana/minimum-wage-ghana/)). Living-wage estimates are GHS 60–100. No source gives casual farm rates directly.
- **Cross-check:** a 2024 Agogo cassava budget puts weeding plus planting at GHS 1,050/acre and land preparation at GHS 300/acre ([AgriGhana Online](https://agrighanaonline.com/2024-high-pricing-of-cassava-sways-farmers-into-large-scale-scale-farming-at-ashanti-akim-agogo/)). The two weeding estimates (770) leave about 280 for planting, which fits.
- **Tractor ploughing GHS 250/acre:** reported 2023–2024, up from GHS 90 in 2021 ([GNA](https://gna.org.gh/2023/09/farmers-decry-high-farm-inputs-prices-as-pfj-ends/), [Graphic Online](https://www.graphic.com.gh/business/business-news/cost-of-maize-up-600-in-5-years-threatens-food-security.html)). Probably higher by 2026. A Tamale price list quoted much lower rates (GHS 80 ploughing, 40 harrowing; [GhanaWeb](https://www.ghanaweb.com/GhanaHomePage/business/Tractor-hire-services-increase-prices-in-Tamale-1258102)), but it appears older.
- **Tractor ridging:** assumed equal to ploughing, since app-based providers charge one per-acre rate per operation ([How We Made It In Africa](https://www.howwemadeitinafrica.com/linking-farmers-with-tractors-the-business-model-of-ghanas-trotro-tractor/146275/)).

### Burundi
- **Wage, 4,300 BIF/day:** FEWS NET's June 2026 casual-labour wage, about half the national average of 8,300 BIF/day ([FEWS NET](https://fews.net/east-africa/burundi/food-security-outlook/june-2026)). Earlier field wages were 1,200–3,000 BIF/day in 2016 ([FAO/WFP crop assessment](https://fscluster.org/sites/default/files/documents/evaluation_des_recoltes_saison_2016c_et_2017a_vf_26fev2017_1.pdf)).
- **Tractors:** no tractor-hire market found, so tractor items are left empty.

## Open issues found along the way

1. **The compute service doesn't read these prices from the API.** Defaults are hardcoded in `R/AkilimoMain.R` (`default_lmo_costs`, NG and TZ only). Adding rows to `default_prices` alone won't change PP advice.
2. **Tanzania defaults disagree:**

   | Item | `R/AkilimoMain.R` (TZS/acre) | API `default_prices` (TZS) |
   |---|---|---|
   | weeding1 | 60,000 | 600,000 |
   | weeding2 | 45,000 | 450,000 |
   | tractor_ridging | 115,000 | 100,000 |

   Both weeding values differ by exactly 10×, which looks like a slipped zero on one side. The other TZ items match.
   **Resolved 2026-11-08:** the API values (600,000 / 450,000) are correct, and the compute service's defaults were corrected to match. Tractor ridging was aligned the same way, to the API's 100,000.
3. **Wrong unit:** the API labels labour items `per_bag`; they are per acre.
4. **Harrowing:** the compute service also has harrowing defaults (manual and tractor), but the API has no harrowing items.

## Before using these

1. Have the IITA RW, GH and BI teams confirm the daily wages and person-days, or replace them with local rates.
2. Then add them to the compute service's `default_lmo_costs`, or make it read them from the price service, and to the API's `default_prices`.
