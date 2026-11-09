# Price review: all default price components, Nov 2026

**IITA Internal Use** · Desk research, 2026-11-08 · Compares the seed values (`database/seeders/data/*.csv`) with public sources. **Nothing here has been applied**; every change needs confirming with the IITA country teams.

Units, all in local currency: fertilisers per 50 kg bag (except Burundi, see below); `cassUP`, sweet potato and starch per tonne; `maize_grain` per kg; `maize_freshCob` per cob; labour per acre. Confidence: **H** = official or primary price list, **M** = reputable press or programme figure, **L** = aggregator, anecdote or derived estimate.

## Headline findings

1. **Fertiliser defaults are stale everywhere, by 1.3× to 7×.** Nigeria is the worst: urea is ₦7,500 against about ₦32,500–50,500.
2. **The price options farmers pick from (`fertilizer_prices`) are far from current prices** in NG, TZ, RW, GH and BI (table below). Their min/max, the "allowed range", would also reject real prices, but nothing enforces it. Checked in `akilimo-mobile` on 2026-11-08: the app never uses `min_allowed_price`/`max_allowed_price`, and its "Exact price" option accepts any value. So the harm is stale options, not rejected input.
3. **Burundi's FOMI prices are per 25 kg bag, not 50 kg.** PNSEB bags have been 25 kg since at least 2023, and the stored values match the 2023 full price of a 25 kg bag.
4. **Cassava root prices (`cassUP`) are weakly evidenced everywhere** and probably understated in TZ, RW and GH. Nigeria is the exception: a 2026 glut reportedly crashed prices. No government series covers fresh roots, so field surveys are needed.
5. **Subsidy policy decides what a default means.** Rwanda subsidises about 45%. Ghana gives fertiliser free to programme farmers in 2026. Burundi's subsidised FOMI was mostly unavailable in 2025–26. Defaults should state whether they are the subsidised or the open-market price.

## Fertiliser price options and their range (`fertilizer_prices`)

The range is the lowest to highest option, apart from the `0` "I don't know" and negative "Exact price" marker rows. The verdicts describe how far the **options** are from the market; the app doesn't enforce the range.

| Country | Options range / bag | Stored defaults | Current market | Verdict |
|---|---|---|---|---|
| NG | 15,201–24,239 | 7,200–22,364 | 32,500–60,000 | Every option is below market. Options around 25,000–70,000 needed |
| TZ | 85,174–135,818 | 53,000–68,000 | 61,000–125,500 (TFRA) | Options miss urea, CAN and MOP market prices (~61,000–77,000); start them around 50,000 |
| RW | 37,185–59,295 | 27,900–35,650 | 52,700–54,500 subsidised; 93,000–99,000 unsubsidised | Options cover subsidised prices only, near the top of the list |
| GH | ~122–160 | 142–160 | 400–550 open market | Every option is far below market. Options around 300–700 needed |
| BI | FOMI 67,000–85,390; dolomite 6,000–16,000 | 77,600–85,390 | 28,000–33,000 subsidised / 112,500+ parallel (per 25 kg) | Options match neither subsidised nor parallel prices |

## By country

### Nigeria (NGN)
| Item | Stored | Researched | Date | Conf. |
|---|---|---|---|---|
| urea | 7,500 | 32,500–37,700 ex-plant; ~50,500 retail | 2026 | M |
| NPK 15-15-15 | 8,500 | 46,900 ex-plant; 50,000–60,000 retail | 2026 | M |
| NPK 20-10-10 | 7,200 | 40,900–44,400 ex-plant; ~43,000 retail | 2026 | M |
| CAN, DAP, MOP, NPK 17-17-17, NPK 20-12-16, SSP, TSP | 7,500–22,364 | no naira price found (DAP import US$775/t, MOP US$400/t) | Jun 2026 | L |
| cassUP | 12,000 | ~5,000 farm-gate, ~25,000 factory-gate (Ogun/Oyo glut) | May 2026 | L |
| maize_grain | 230 /kg | 257–409 wholesale; ~816 retail | Aug–Oct 2026 | M |
| maize_freshCob | 50 /cob | ~500 (one Lagos market) | Jul 2026 | L |
| tractor_ploughing | 6,000 | ~14,000–18,000 | 2026 | L |
| manual labour, tractor ridging | 6,000–30,000 | no per-operation rates | – | – |
| starch factories | 11,500–25,000 | ~25,000 factory-gate (glut) | May 2026 | L |

Sources: [Indorama price list](https://www.indoramafertilizers.com/article/33/fertiliser.html), [Agriculture Nigeria](https://www.agriculturenigeria.com/?p=22231), [The Guardian NG (import prices)](https://guardian.ng/?p=2859513), [The Guardian NG (cassava glut)](https://guardian.ng/?p=2931331), [Mansa Markets maize](https://www.mansamarkets.com/commodities/maize/nigeria), [Gazette NG](https://gazettengr.com/?p=486933), [HTS Farms](https://htsfarms.ng/?p=40989).

### Tanzania (TZS)
| Item | Stored | Researched (TFRA indicative, national average) | Date | Conf. |
|---|---|---|---|---|
| urea | 58,000 | 77,088 | Sep 2025 | H |
| CAN | 53,000 | 60,972 | Sep 2025 | H |
| DAP | 60,000 | 125,537 | Sep 2025 | H |
| MOP | 67,000 | 75,967 | Sep 2025 | H |
| NPK 17-17-17 / 20-10-10 | 63,000 / 68,000 | 89,154 (all NPK grades) | Sep 2025 | M |
| TSP | 64,000 | 103,078 | Sep 2025 | H |
| cassUP | 180,000 | ~397,000 (FAOSTAT-derived); traders in Tanga ~670,000–830,000 | 2024–26 | L |
| sweetPotato_tuber | 120,000 | ~375,000 (FAOSTAT-derived) | Jun 2026 | L |
| sweetPotato_flour | 384,000 | no data | – | – |
| tractor_ploughing | 150,000 | 40,000–120,000 | ~2024 | M |
| manual labour, tractor ridging, weeding | 45,000–225,000 | no per-operation rates; statutory agriculture minimum ~5,833/day | 2026 | L |
| starch factories (FJS, CSTC, Dar Canton) | 83,333–125,000 | no current price | – | – |

TFRA prices are unsubsidised wholesale indicative prices; the subsidised farmer price for 2025/26 was not found. Sources: TFRA price notices for [urea](https://www.tfra.go.tz/uploads/documents/sw-1757666513-UREA.pdf), [CAN](https://www.tfra.go.tz/uploads/documents/sw-1757666780-CAN.pdf), [DAP](https://www.tfra.go.tz/uploads/documents/sw-1757668067-DAP.pdf), [MOP](https://www.tfra.go.tz/uploads/documents/sw-1757667149-MOP.pdf), [NPKs](https://www.tfra.go.tz/uploads/documents/sw-1757667893-NPKs.pdf), [TSP](https://www.tfra.go.tz/uploads/documents/sw-1757667964-TSP.pdf); [IPP Media](https://www.ippmedia.com/the-guardian/business/read/hopes-for-farmers-as-cassava-prices-increase-in-tanga-2024-04-23-115602), [The Citizen](https://thecitizen.co.tz/tanzania/news/national/agriculture-mechanisation-bashe-wants-private-dealers-registered-4769146), [Selina Wamucii cassava](https://www.selinawamucii.com/insights/prices/tanzania/cassava/), [Selina Wamucii sweet potatoes](https://www.selinawamucii.com/insights/prices/tanzania/sweet-potatoes/).

### Rwanda (RWF)
| Item | Stored | Researched, season 2027A (subsidised farmer / full price) | Date | Conf. |
|---|---|---|---|---|
| urea | 28,200 | ~54,500 / 99,000 | Jul 2026 | H |
| DAP | 31,650 | ~53,000 / 97,500 | Jul 2026 | H |
| NPK 17-17-17 | 35,650 | ~52,700 / 93,050 | Jul 2026 | H |
| MOP | 27,900 | not in the subsidy list | – | – |
| cassUP | 75,000 | ~140,000 (Kamonyi traders, undated); 300,000–413,000 (Esoko farm-gate, Jul 2026) | – | L |
| labour | none (drafts 12,000–20,000) | wage ~2,000/day plausible but undated; 2018 survey mean ~750 | – | L |
| Kinazi fresh roots | draft 330/kg | 75–100/kg in 2012–2019; nothing current | – | L |

Sources: [allAfrica (2027A prices)](https://allafrica.com/stories/202607030438.html), [Umunota](https://umunota.com/?p=77885), [The New Times (subsidies)](https://www.newtimes.co.rw/article/28400/news/agriculture/rwf75bn-in-agric-subsidies-this-is-how-much-farmers-will-pay), [The New Times (cassava prices)](https://www.newtimes.co.rw/article/170587/News/cassava-farmers-decry-low-prices), [TangaBid/Esoko](https://www.tangabid.com/market/prices/?product=9), [KT Press](https://www.ktpress.rw/2017/09/cassava-farmers-condition-kinazi-factory-to-pay-on-spot/), [The EastAfrican](https://www.theeastafrican.co.ke/tea/rwanda-today/kinazi-factory-losing-out-as-traders-offer-more-for-cassava--1311324).

### Ghana (GHS)
| Item | Stored | Researched | Date | Conf. |
|---|---|---|---|---|
| NPK, all grades | 146–160 | 400–550 open market; 410–420 online retailer | 2023; listing undated | M/L |
| urea | 142 | nothing current; 300–350 was projected for 2022 | 2022 | L |
| programme fertiliser | – | free under Feed Ghana in 2026 (subsidy dropped) | Mar 2026 | H |
| cassUP | 450 | ~2,470 (FAOSTAT-derived); Accra GH₵600 per large sack | 2023–26 | L |
| tractor_ploughing | draft 250 | ~250 (up from 90 in 2021) | 2023–24 | M |
| rural wage | draft 55/day | minimum 19.97–21.77/day; hired cocoa labour ~122/day | 2025 | L |
| Ayensu starch | draft 900 | 350 is still the only published figure | 2021 | L |

Sources: [GNA](https://gna.org.gh/2023/09/farmers-decry-high-farm-inputs-prices-as-pfj-ends/), [Ghana Chemicals](https://ghanachemicalsltd.com/product/npk-20-10-10/), [AGRA Price & Policy Monitor](https://agra.org/wp-content/uploads/2022/08/Price-and-Policy-Monitor-July-edition-4.pdf), [Ecofin](https://www.ecofinagency.com/news-agriculture/2403-54025-ghana-drops-fertilizer-subsidies-for-free-distribution-amid-price-spike), [Further Africa](https://furtherafrica.com/2022/02/15/ifdc-commits-to-make-fertilizer-affordable-in-ghana/), [Citi Newsroom](https://www.citinewsroom.com/2026/04/channel-one-tv-market-price-barometer-tomatoes-cassava-among-high-priced-staples/), [Graphic Online](https://www.graphic.com.gh/business/business-news/cost-of-maize-up-600-in-5-years-threatens-food-security.html), [Fairtrade](https://www.fairtrade.net/content/dam/fairtrade/global/why-we-do-it/living-income/living-income-reference-prices/Fairtrade-Living-Income-Reference-Prices-Cocoa-Ghana-Cote-dIvoire-2025-explanatory-note.pdf).

### Burundi (BIF)
| Item | Stored (labelled 50 kg) | Researched, per **25 kg** bag (full cost / farmer pays) | Date | Conf. |
|---|---|---|---|---|
| FOMI-Imbura | 85,390 | 86,408 / 31,000 | Sep 2023 | M |
| FOMI-Bagara | 77,600 | 77,473 / 28,000 | Sep 2023 | M |
| FOMI-Totahaza | 77,600 | 91,965 / 33,000 (unverified snippet) | ~2023 | L |
| FOMI, actual 2025–26 | – | official ~33,000; parallel market 4,500–5,000/kg (~112,500–125,000) | Aug 2026 | M |
| dolomite (50 kg) | range 6,000–16,000 | 19,000 / 5,000 | Sep 2023 | M |
| cassUP | 700,000 | no data; cassava flour ~1,843–2,618/kg retail | 2025 | L |
| rural wage | draft 4,300/day | 2,000–5,000/day informal; FEWS NET 4,300 (Jun 2026) | 2025–26 | L–M |
| starch factories | none | only lead: BCFA (Bujumbura start-up, status unknown) | – | L |

The official rate is ~2,910–3,000 BIF/USD against a parallel premium of about 100% (IMF, Apr 2026), so any USD conversion is unreliable. Sources: [Le Renouveau](https://lerenouveau.bi/le-ministre-de-lagriculture-revele-trois-raisons-qui-justifient-la-hausse-du-prix-de-lengrais-organo-mineral), [ABP](https://english.abpinfo.bi/2023/09/15/status-of-preparations-for-the-2024-a-growing-season/), [FORSC](https://forscburundi.org/wp-content/uploads/2026/08/Declaration-Problematique-des-Intrants.pdf), [Selina Wamucii flour](https://www.selinawamucii.com/insights/prices/burundi/cassava-flour/), [Tridge](https://www.tridge.com/intelligences/cassava-tapioca-flour/BI/price), [Africarrières](https://africarrieres.com/burundi/en/guide/marche-emploi/salary-guide), [VC4A (BCFA)](https://vc4a.com/ventures/burundi-cassava-flour-and-amido-bcfa/), [Burundi Times](https://www.burunditimes.com/?p=8131).

## Changes to the earlier drafts

- **Ghana labour** ([labour-costs-rw-gh-bi.md](labour-costs-rw-gh-bi.md)): hired farm labour looks closer to ~110–120 GHS/day than the 55 assumed, so the manual-operation drafts (330–550/acre) may be up to 2× low.
- **Kinazi starch** ([starch-factories-rw-gh-bi.md](starch-factories-rw-gh-bi.md)): the evidence conflicts. Reports from 2012–2019 have Kinazi paying 75–100 RWF/kg. Esoko's July 2026 farm-gate series for Ruhango shows 350 RWF/kg, but this review rates that series low-confidence. Keep the factory inactive until KCP quotes a price.
- **Ayensu starch:** if farm-gate prices really are ~2,000–2,500 GHS/t, the 900 GHS/t draft is low.

## Suggested next steps

1. Ask each country team for current subsidised and open-market fertiliser prices, and decide which one the default represents.
2. Fix the Burundi fertiliser bag size: store prices per 25 kg, or convert them to 50 kg.
3. Bring the `fertilizer_prices` options up to date (NG, TZ, RW, GH, BI), ideally by generating them from the catalogue (phase 3 of `docs/PRICE_CATALOGUE_DESIGN.md`) and keeping the `0` and negative marker rows.
4. ~~Update Tanzania's fertiliser defaults from the TFRA notices; they are the only high-confidence official series found.~~ **Done** in `87721bc` (TFRA September 2025 national averages, rounded to 100 TZS).
5. Get field prices for cassava roots and labour before changing `cassUP` or labour defaults.
