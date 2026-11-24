import {COUNTRY_INTERCROP} from '../constants'
import type {Step} from '../types'

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
        planting_date_cultivar_rec: s === 'PDC',
        inter_cropping_maize_rec:  (s === 'IC' || s === 'COMPLETE') && intercropCrop === 'MAIZE',
        inter_cropping_potato_rec: (s === 'IC' || s === 'COMPLETE') && intercropCrop === 'POTATO',
    }
}

/** Whether the request needs a second-crop price (intercropping maize or sweet potato). */
export function needsSecondCrop(s: string, country: string): 'MAIZE' | 'POTATO' | null {
    if (s !== 'IC' && s !== 'COMPLETE') return null
    return COUNTRY_INTERCROP[country] ?? null
}
