import {COUNTRY_INTERCROP} from '../constants'
import type {FertilizerEntry, FormState, Step} from '../types'
import {maxHarvestDate, minHarvestDate, plantingMin} from './dates'
import {needsFertilizers, needsSecondCrop} from './scenario'

/** The errors of one wizard step, keyed by the form field (or `price_<fertilizer key>`). */
export function validateStep(s: Step, form: FormState, fertilizers: FertilizerEntry[]): Record<string, string> {
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
