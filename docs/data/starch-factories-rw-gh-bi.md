# Starch factories and root prices: Rwanda, Ghana, Burundi

**IITA Internal Use** · Draft reference, researched 2026-11-08 · **Not seeded**: nothing here is loaded into the database.

The API has starch prices only for Nigeria and Tanzania (`database/seeders/data/starch_prices.csv`). This note records what public sources say about factories that buy fresh cassava roots in Rwanda, Ghana and Burundi, and gives *probable* prices. Every price below is an estimate from public, mostly dated sources. Confirm each one with the factory (or the IITA country team) before it goes into `starch_prices.csv`.

Draft rows in the seeder's format, plus source and confidence columns: [`starch_prices_candidates.csv`](starch_prices_candidates.csv). Like the Tanzanian factories, each has a single price class (`minStarch` 0, `>0`), because no source describes payment by starch content.

## Summary

| Country | Factory | Buys fresh roots? | Probable price (fresh roots) | Range | Confidence |
|---|---|---|---|---|---|
| GH | Ayensu Starch Company (ASCo), Bawjiase, Central Region | Yes; food-grade starch, ~1,000 outgrowers | **GHS 900 / t** | 350–2,470 | Low |
| GH | Caltech Ventures, Hodzo, Volta Region | Planned (ethanol, high-quality cassava flour); operating status not confirmed | none | none | None |
| RW | Kinazi Cassava Plant (KCP), Ruhango, Southern Province | Yes; wet-milled flour, not starch | **RWF 330,000 / t** (330 / kg) | 300,000–413,000 | Low |
| BI | None found | none | none | none | none |

## Ghana

**Ayensu Starch Company (ASCo).** Food-grade starch, commissioned 2004, designed for about 22,000 t of roots a year. Restarted around 2021 with about 1,000 outgrowers, it needs 400 t/day but could secure only 150–200 t.
- Only published price: **GHS 350 per tonne** (factory manager, December 2021). Farmers called it too low. ([GNA, Dec 2021](https://gna.org.gh/2021/12/ayensu-starch-factory-to-transform-rural-economy-with-5000-jobs/), [Modern Ghana](https://www.modernghana.com/news/1126347/cr-cassava-farmers-jubilate-over-revival-of.html), [Graphic Online](https://www.graphic.com.gh/news/general-news/ayensu-starch-factory-bounces-back.html))
- Probable price GHS 900/t: the 2021 price scaled by roughly 2.5× cumulative Ghana consumer-price inflation since late 2021. That multiplier is approximate.
- Upper bound GHS 2,470/t: a national farmgate figure of about 2.47 GHS/kg ([Selina Wamucii](https://www.selinawamucii.com/insights/prices/ghana/cassava/)). That aggregator's underlying data is older and doesn't reconcile with ASCo's price, so it bounds the range rather than estimating it.
- Rural wholesale prices rose from about GHS 172/t (2009) to GHS 449/t (2018) ([IntechOpen](https://www.intechopen.com/chapters/76626)).

**Caltech Ventures.** Planned ethanol and high-quality cassava flour plant on 2,500 ha at Hodzo, Volta Region ([Advanced BioFuels USA](https://advancedbiofuelsusa.info/ghanas-first-ethanol-plant-to-begin-production), [GEPA](https://www.gepaghana.org/import/ghana-product/ethanol/)). Not a starch factory, and no evidence it is operating or what it pays. Listed only as a lead.

## Rwanda

> **Update (price review, Nov 2026):** a second search found only 2012–2019 reports of Kinazi paying 75–100 RWF/kg, and rated the Esoko 2026 farm-gate series low-confidence. The 330 RWF/kg estimate is therefore disputed; keep the factory inactive until KCP quotes a price. See [price-review-2026-11.md](price-review-2026-11.md).

**Kinazi Cassava Plant (KCP).** Wet-mills fresh roots into fine flour, not starch ([kcp.rw](https://kcp.rw/)). It buys fresh roots by weight, which is what AKILIMO's starch-factory option models, so it is the best fit available. Whether to list a flour mill as a "starch factory" is a product decision.
- Historic prices paid: **RWF 90/kg** (2019, [KT Press](https://www.ktpress.rw/2019/01/rwandas-kinazi-cassava-plant-targets-three-key-u-s-markets/)) and as low as **RWF 75/kg** (around 2020, [The EastAfrican](https://www.theeastafrican.co.ke/tea/rwanda-today/cassava-maize-farmers-in-rwanda-count-losses-as-prices-drop-1390034)); 60–110 RWF/kg in 2011 ([WUR thesis](https://edepot.wur.nl/298432)).
- Current farmgate (Esoko Rwanda, July 2026): **350 RWF/kg in Ruhango**, KCP's district ([TangaBid](https://www.tangabid.com/market/prices/160018/)); 300–413 RWF/kg in other markets ([TangaBid](https://www.tangabid.com/market/prices/?product=9)).
- Probable price RWF 330/kg: assumes KCP pays slightly under local farmgate, as it did before. The big jump from the 2019–2020 prices tracks market prices, not a published KCP price.

## Burundi

No cassava starch factory, or other large fresh-root processor, was found. Burundi imported only about 137 kg of cassava starch in 2023 ([WITS](https://wits.worldbank.org/trade/comtrade/en/country/BDI/year/2023/tradeflow/Imports/partner/ALL/product/110814)), which doesn't suggest a local starch industry. Market prices found are for cassava *flour* in Bujumbura, 1,300–2,200 FBu/kg in 2022–2023 ([Le Renouveau](https://lerenouveau.bi/prix-des-denrees-alimentaires-au-marche-sion-dans-la-ville-de-bujumbura-en-date-du-14-02-2023/)), and say nothing about root prices paid by a factory. **No rows are proposed for Burundi**; the starch-factory option should stay off there unless the IITA Burundi team knows of a buyer.

## Before using these rows

1. Confirm the factory is operating and buying roots, and get the current price per tonne of fresh roots, plus any grading by starch content (which would add price classes).
2. Get the GH and RW prices confirmed by the factories or the IITA country teams.
3. Then append the rows (without the extra columns) to `database/seeders/data/starch_prices.csv`, and add the factories to `starch_factories`.
