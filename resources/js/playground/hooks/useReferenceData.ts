import {useEffect, useState} from 'react'
import {
    fetchCassavaPrices, fetchCassavaUnits, fetchCountries, fetchFertilizerPrices,
    fetchFertilizers, fetchMaizePrices, fetchPotatoPrices,
} from '../api'
import {COUNTRY_INTERCROP, DEFAULT_CASSAVA_UNITS} from '../constants'
import {typicalPrice} from '../lib/prices'
import {needsFertilizers} from '../lib/scenario'
import type {CassavaUnit, Country, FertilizerEntry, PriceHints} from '../types'

export const NO_HINTS: PriceHints = {currency: null, cassavaPerTonne: null, maizeCob: null, maizeGrain: null, sweetPotatoPerTonne: null}

/**
 * What the API knows that the form needs: the countries and cassava units, the chosen
 * country's price hints and the fertilizers it sells. `onPriceHints` is called when a
 * new country's prices arrive, so the form can prefill them.
 */
export function useReferenceData(
    country: string,
    scenario: string,
    onPriceHints: (hints: PriceHints) => void,
) {
    const [countries, setCountries] = useState<Country[]>([])
    const [cassavaUnits, setCassavaUnits] = useState<CassavaUnit[]>(DEFAULT_CASSAVA_UNITS)
    const [priceHints, setPriceHints] = useState<PriceHints>(NO_HINTS)
    const [fertilizers, setFertilizers] = useState<FertilizerEntry[]>([])
    const [loadingFerts, setLoadingFerts] = useState(false)

    useEffect(() => {
        fetchCountries().then(setCountries).catch(() => {})
        fetchCassavaUnits()
            .then((units) => {
                const active = units.filter((u) => u.active !== false && u.unit_weight > 0)
                if (active.length) setCassavaUnits(active.sort((a, b) => a.unit_weight - b.unit_weight))
            })
            .catch(() => {})
    }, [])

    // Prices for the chosen country, to prefill the crop prices
    useEffect(() => {
        if (!country) return
        let cancelled = false
        Promise.all([
            fetchCassavaPrices(country),
            COUNTRY_INTERCROP[country] === 'MAIZE' ? fetchMaizePrices(country) : Promise.resolve([]),
            COUNTRY_INTERCROP[country] === 'POTATO' ? fetchPotatoPrices(country) : Promise.resolve([]),
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
            onPriceHints(hints)
        }).catch(() => setPriceHints(NO_HINTS))
        return () => { cancelled = true }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [country])

    useEffect(() => {
        if (!country || !needsFertilizers(scenario)) return
        let cancelled = false
        setLoadingFerts(true)
        setFertilizers([])
        Promise.all([
            fetchFertilizers(country),
            fetchFertilizerPrices(country).catch(() => []),
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
    }, [country, scenario])

    function resetReferenceData() {
        setPriceHints(NO_HINTS)
        setFertilizers([])
    }

    return {countries, cassavaUnits, priceHints, fertilizers, setFertilizers, loadingFerts, resetReferenceData}
}
