import {useEffect, useReducer, useRef, useState} from 'react'
import type {ApiResponse} from './RecommendationResult'
import type {CassavaUnit, Country, FertilizerEntry, FormState, PriceHints, Step} from './types'
import {COUNTRY_INTERCROP, DEFAULT_CASSAVA_UNITS, INITIAL_FORM_STATE} from './constants'

// ── Date helpers ──────────────────────────────────────────────────────────────

const today = new Date()

const sixMonthsAgo = new Date(today)
sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6)
export const plantingMin = sixMonthsAgo.toISOString().slice(0, 10)

function addMonthsDays(base: Date, months: number, days = 0): string {
    const d = new Date(base)
    d.setMonth(d.getMonth() + months)
    if (days) d.setDate(d.getDate() + days)
    return d.toISOString().slice(0, 10)
}

export function minHarvestDate(planting: string): string {
    return addMonthsDays(planting ? new Date(planting) : today, 7, 24)
}

export function maxHarvestDate(planting: string): string {
    return addMonthsDays(planting ? new Date(planting) : today, 15)
}

/** A typical cassava season: harvest twelve months after planting. */
export function typicalHarvestDate(planting: string): string {
    return addMonthsDays(new Date(planting), 12)
}

// ── Step helpers ──────────────────────────────────────────────────────────────

export function needsFertilizers(s: string) {
    return s === 'FR' || s === 'COMPLETE'
}

export function totalSteps(s: string): Step {
    return needsFertilizers(s) ? 5 : 4
}

export function scenarioToFlags(s: string, intercropCrop: string) {
    return {
        fertilizer_rec:            s === 'FR'   || s === 'COMPLETE',
        planting_practices_rec:    s === 'PP'   || s === 'COMPLETE',
        scheduled_planting_rec:    s === 'SPHS' || s === 'COMPLETE',
        scheduled_harvest_rec:     s === 'SPHS' || s === 'COMPLETE',
        inter_cropping_maize_rec:  (s === 'IC' || s === 'COMPLETE') && intercropCrop === 'MAIZE',
        inter_cropping_potato_rec: (s === 'IC' || s === 'COMPLETE') && intercropCrop === 'POTATO',
    }
}

/** Whether the request needs a second-crop price (intercropping maize or sweet potato). */
export function needsSecondCrop(s: string, country: string): 'MAIZE' | 'POTATO' | null {
    if (s !== 'IC' && s !== 'COMPLETE') return null
    return COUNTRY_INTERCROP[country] ?? null
}

// ── Price lists ───────────────────────────────────────────────────────────────

interface PriceBand {
    min_local_price?: number | null
    max_local_price?: number | null
    average_price?: number | null
    price_per_bag?: number | null
}

/**
 * The middle band's price, skipping the 0 / -1 placeholder rows the lists use for
 * "exact price" and "I don't know"; null when the list has no real band.
 */
function typicalPrice(bands: PriceBand[], field: 'average_price' | 'price_per_bag' = 'average_price'): number | null {
    const prices = bands
        .map((b) => Number(b[field] ?? ((Number(b.min_local_price) + Number(b.max_local_price)) / 2)))
        .filter((p) => Number.isFinite(p) && p > 0)
        .sort((a, b) => a - b)
    return prices.length ? prices[Math.floor(prices.length / 2)] : null
}

/** All rows of an API list, following its pages (the API pages at 50–100 rows). */
async function getList<T>(url: string): Promise<T[]> {
    const rows: T[] = []
    let next: string | null = url
    for (let page = 0; next && page < 10; page++) {
        const r: Response = await fetch(next, {headers: {Accept: 'application/json'}})
        if (!r.ok) break
        const d = await r.json()
        rows.push(...((d.data ?? d) as T[]))
        next = d.links?.next ?? null
    }
    return rows
}

function roundPrice(p: number): string {
    return String(p >= 100 ? Math.round(p) : Math.round(p * 100) / 100)
}

const NO_HINTS: PriceHints = {currency: null, cassavaPerTonne: null, maizeCob: null, maizeGrain: null, sweetPotatoPerTonne: null}

// ── Hook ─────────────────────────────────────────────────────────────────────

export function usePlaygroundForm() {
    const [step, setStep] = useState<Step>(1)
    const [form, setFormState] = useReducer(
        (prev: FormState, patch: Partial<FormState>) => ({...prev, ...patch}),
        INITIAL_FORM_STATE,
    )
    const [countries, setCountries] = useState<Country[]>([])
    const [cassavaUnits, setCassavaUnits] = useState<CassavaUnit[]>(DEFAULT_CASSAVA_UNITS)
    const [priceHints, setPriceHints] = useState<PriceHints>(NO_HINTS)
    const [fertilizers, setFertilizers] = useState<FertilizerEntry[]>([])
    const [loadingFerts, setLoadingFerts] = useState(false)
    const [submitting, setSubmitting] = useState(false)
    const [result, setResult] = useState<ApiResponse | null>(null)
    const [apiError, setApiError] = useState<string | null>(null)
    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({})
    const resultRef = useRef<HTMLDivElement>(null)

    useEffect(() => {
        getList<Country>('/api/v1/countries').then(setCountries).catch(() => {})
        getList<CassavaUnit & {active?: boolean}>('/api/v1/cassava-units')
            .then((units) => {
                const active = units.filter((u) => u.active !== false && u.unit_weight > 0)
                if (active.length) setCassavaUnits(active.sort((a, b) => a.unit_weight - b.unit_weight))
            })
            .catch(() => {})
    }, [])

    /** Update fields and drop the errors about them, so a fixed field stops showing one. */
    function setForm(patch: Partial<FormState>) {
        setFormState(patch)
        const keys = Object.keys(patch)
        if (keys.includes('mapLat') || keys.includes('mapLong')) keys.push('mapLat', 'mapLong')
        setValidationErrors((prev) => {
            if (!keys.some((k) => k in prev)) return prev
            const next = {...prev}
            keys.forEach((k) => delete next[k])
            return next
        })
    }

    // Prices for the chosen country, to prefill the crop prices
    useEffect(() => {
        if (!form.country) return
        let cancelled = false
        const code = form.country
        Promise.all([
            getList<PriceBand & {currency?: {currency_code?: string} | null}>(`/api/v1/cassava-prices/country/${code}`),
            COUNTRY_INTERCROP[code] === 'MAIZE' ? getList<PriceBand & {produce_type?: string}>(`/api/v1/maize-prices/country/${code}`) : Promise.resolve([]),
            COUNTRY_INTERCROP[code] === 'POTATO' ? getList<PriceBand>(`/api/v1/potato-prices/country/${code}`) : Promise.resolve([]),
        ]).then(([cassava, maize, potato]) => {
            if (cancelled) return
            const hints: PriceHints = {
                currency: cassava.find((b) => b.currency?.currency_code)?.currency?.currency_code ?? null,
                cassavaPerTonne: typicalPrice(cassava),
                maizeCob: typicalPrice(maize.filter((b) => b.produce_type === 'cob')),
                maizeGrain: typicalPrice(maize.filter((b) => b.produce_type === 'grain')),
                sweetPotatoPerTonne: typicalPrice(potato),
            }
            setPriceHints(hints)
            // A new country means a new currency, so its prices replace any earlier ones
            setForm({
                cassavaPrice: hints.cassavaPerTonne ? roundPrice(hints.cassavaPerTonne * Number(form.cassavaUnitWeight) / 1000) : '',
                maizePrice: (form.maizeProduceType === 'grain' ? hints.maizeGrain : hints.maizeCob)?.toString() ?? '',
                sweetPotatoPrice: hints.sweetPotatoPerTonne ? roundPrice(hints.sweetPotatoPerTonne) : '',
            })
        }).catch(() => setPriceHints(NO_HINTS))
        return () => { cancelled = true }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [form.country])

    useEffect(() => {
        if (!form.country || !needsFertilizers(form.scenario)) return
        let cancelled = false
        setLoadingFerts(true)
        setFertilizers([])
        Promise.all([
            getList<{fertilizer_key: string; name: string; type?: string; weight: number; available: boolean}>(
                `/api/v1/fertilizers/country/${form.country}?per_page=50`),
            getList<PriceBand & {fertilizer_key: string; active?: boolean}>(
                `/api/v1/fertilizer-prices/country/${form.country}?per_page=100`).catch(() => []),
        ])
            .then(([items, prices]) => {
                if (cancelled) return
                setFertilizers(
                    items
                        .filter((f) => f.available)
                        .map((f) => ({
                            key:             f.fertilizer_key,
                            name:            f.name,
                            fertilizer_type: f.type || f.fertilizer_key,
                            weight:          f.weight ?? 50,
                            // Typical bag price from the country's list; the user can change it
                            price:           typicalPrice(prices.filter((p) => p.fertilizer_key === f.fertilizer_key && p.active !== false), 'price_per_bag') ?? 0,
                            selected:        false,
                        })),
                )
            })
            .catch(() => {})
            .finally(() => { if (!cancelled) setLoadingFerts(false) })
        return () => { cancelled = true }
    }, [form.country, form.scenario])

    function handleCountryChange(code: string) {
        const intercropCrop = COUNTRY_INTERCROP[code]
        // The map zooms to the country, but the farm pin is the user's to place:
        // a pin in the country's centre would give advice for the wrong place.
        setForm({
            country:  code,
            mapLat:   '',
            mapLong:  '',
            ...(intercropCrop ? {intercropCrop} : {}),
        })
    }

    /** Change the cassava selling unit, keeping the same price per kg (15,000/t = 750 per 50 kg). */
    function setCassavaUnit(kg: number) {
        const oldKg = Number(form.cassavaUnitWeight) || 1000
        const price = Number(form.cassavaPrice)
        setForm({
            cassavaUnitWeight: String(kg),
            ...(price > 0 ? {cassavaPrice: roundPrice(price * kg / oldKg)} : {}),
        })
    }

    function setMaizeProduceType(type: string) {
        const typical = type === 'grain' ? priceHints.maizeGrain : priceHints.maizeCob
        setForm({maizeProduceType: type, ...(typical ? {maizePrice: String(typical)} : {})})
    }

    function updateFert(key: string, patch: Partial<FertilizerEntry>) {
        setFertilizers((prev) => prev.map((f) => (f.key === key ? {...f, ...patch} : f)))
        setValidationErrors((prev) => {
            const next = {...prev}
            delete next[`price_${key}`]
            if (patch.selected) delete next.fertilizers
            return next
        })
    }

    function validateStep(s: Step): Record<string, string> {
        const errs: Record<string, string> = {}

        if (s === 2) {
            if (!form.country) errs.country = 'Please select a country'
            else if (form.scenario === 'IC' && !COUNTRY_INTERCROP[form.country])
                errs.country = 'Intercropping is only available for Nigeria (maize) and Tanzania (potato)'
            if (!form.fieldSize || +form.fieldSize < 0.1) errs.fieldSize = 'Enter a valid field size (min 0.1)'
            if (!form.mapLat || isNaN(+form.mapLat))   errs.mapLat  = 'Click the map to pin your farm location'
            if (!form.mapLong || isNaN(+form.mapLong)) errs.mapLong = 'Click the map to pin your farm location'
        }

        if (s === 3) {
            const fy = Number(form.fieldYield)
            if (isNaN(fy) || fy < 0 || fy > 100)
                errs.fieldYield = 'Enter your current cassava yield (0–100 t/ha)'
            const sq = Number(form.soilQuality)
            if (isNaN(sq) || sq < 0 || sq > 5)
                errs.soilQuality = 'Soil quality must be between 0 and 5'
            if (!(Number(form.cassavaPrice) > 0))
                errs.cassavaPrice = 'Enter the price you get for your cassava'
            const second = needsSecondCrop(form.scenario, form.country)
            if (second === 'MAIZE' && !(Number(form.maizePrice) > 0))
                errs.maizePrice = 'Enter the price you get for your maize'
            if (second === 'POTATO' && !(Number(form.sweetPotatoPrice) > 0))
                errs.sweetPotatoPrice = 'Enter the price you get for your sweet potato'
        }

        if (s === 4 && needsFertilizers(form.scenario)) {
            if (!fertilizers.some((f) => f.selected))
                errs.fertilizers = 'Select at least one fertilizer'
            fertilizers.filter((f) => f.selected).forEach((f) => {
                if (!f.price || f.price <= 0)
                    errs[`price_${f.key}`] = 'Enter a price for this fertilizer'
            })
        }

        const dateStep = needsFertilizers(form.scenario) ? 5 : 4
        if (s === dateStep) {
            if (!form.plantingDate) {
                errs.plantingDate = 'Enter a planting date'
            } else if (form.plantingDate < plantingMin) {
                errs.plantingDate = 'Planting date cannot be more than 6 months in the past'
            }
            if (!form.harvestDate) {
                errs.harvestDate = 'Enter a harvest date'
            } else if (form.plantingDate && form.harvestDate < minHarvestDate(form.plantingDate)) {
                errs.harvestDate = 'Harvest date must be at least ~7.8 months after planting date'
            } else if (form.plantingDate && form.harvestDate > maxHarvestDate(form.plantingDate)) {
                errs.harvestDate = 'Harvest date must be within 15 months of planting date'
            }
            if (![0, 1, 2].includes(Number(form.plantingDateWindow)))
                errs.plantingDateWindow = 'Select 0, 1, or 2 months'
            if (![0, 1, 2].includes(Number(form.harvestDateWindow)))
                errs.harvestDateWindow = 'Select 0, 1, or 2 months'
        }

        return errs
    }

    function advance() {
        const errs = validateStep(step)
        if (Object.keys(errs).length) { setValidationErrors(errs); return }
        setValidationErrors({})
        setStep((prev) => (prev + 1) as Step)
    }

    function back() {
        setValidationErrors({})
        setStep((prev) => (prev - 1) as Step)
    }

    async function submit() {
        const lastStep = totalSteps(form.scenario)
        const errs = validateStep(lastStep)
        if (Object.keys(errs).length) { setValidationErrors(errs); return }
        setValidationErrors({})
        setSubmitting(true)
        setApiError(null)
        setResult(null)

        const todayStr      = today.toISOString().slice(0, 10)
        const defaultHarvest = minHarvestDate(todayStr)
        const fertList = fertilizers.length
            ? fertilizers
            : [{key: 'none', name: 'None', fertilizer_type: 'NONE', weight: 50, price: 0, selected: false}]
        const second = needsSecondCrop(form.scenario, form.country)

        const payload = {
            country_code:              form.country,
            use_case:                  form.scenario === 'COMPLETE' ? 'FR' : form.scenario,
            field_size:                Number.parseFloat(form.fieldSize),
            area_unit:                 form.areaUnit,
            map_lat:                   Number.parseFloat(form.mapLat),
            map_long:                  Number.parseFloat(form.mapLong),
            lang:                      form.lang,
            field_yield:               Number.parseInt(form.fieldYield, 10),
            soil_quality:              Number.parseFloat(form.soilQuality),
            risk_attitude:             Number.parseInt(form.riskAttitude, 10),
            cassava_produce_type:      form.cassavaProduceType,
            cassava_unit_price:        Number.parseFloat(form.cassavaPrice),
            cassava_unit_weight:       Number.parseFloat(form.cassavaUnitWeight),
            maize_produce_type:        form.maizeProduceType,
            maize_unit_price:          second === 'MAIZE' ? Number.parseFloat(form.maizePrice) : null,
            sweet_potato_produce_type: form.sweetPotatoProduceType,
            sweet_potato_unit_price:   second === 'POTATO' ? Number.parseFloat(form.sweetPotatoPrice) : null,
            planting_date:             form.plantingDate || todayStr,
            harvest_date:              form.harvestDate  || defaultHarvest,
            planting_date_window:      Number.parseInt(form.plantingDateWindow, 10) || 0,
            harvest_date_window:       Number.parseInt(form.harvestDateWindow, 10)  || 0,
            fertilizer_list:           fertList,
            // Only the country's own intercrop (maize in Nigeria, sweet potato in Tanzania)
            ...scenarioToFlags(form.scenario, second ?? ''),
        }

        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''
        try {
            const res = await fetch('/playground/compute', {
                method:  'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                body:    JSON.stringify(payload),
            })
            const json = await res.json()
            if (res.ok) {
                setResult(json)
                setTimeout(() => resultRef.current?.scrollIntoView({behavior: 'smooth', block: 'start'}), 100)
            } else {
                setApiError(json.message ?? `Error ${res.status}`)
            }
        } catch {
            setApiError('Network error — please check your connection and try again.')
        } finally {
            setSubmitting(false)
        }
    }

    function reset() {
        setResult(null)
        setApiError(null)
        setStep(1)
        setFormState(INITIAL_FORM_STATE)
        setValidationErrors({})
        setPriceHints(NO_HINTS)
        setFertilizers([])
    }

    return {
        step, form, setForm,
        countries, cassavaUnits, priceHints, fertilizers, loadingFerts,
        submitting, result, apiError, validationErrors,
        resultRef,
        handleCountryChange, setCassavaUnit, setMaizeProduceType, updateFert,
        advance, back, submit, reset,
    }
}
