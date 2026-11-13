import type {IntercropCrop, Scenario} from './types'

export const SCENARIOS: {id: Scenario; icon: string; title: string; desc: string}[] = [
    {id: 'FR',       icon: '🌱',  title: 'Fertilizer Recommendations', desc: 'Which fertilizers to apply and in what quantities'},
    {id: 'IC',       icon: '🌽',  title: 'Intercropping',              desc: 'Should I grow cassava alongside maize or potato?'},
    {id: 'PP',       icon: '🌿',  title: 'Planting Practices',         desc: 'Land preparation methods and field operations'},
    {id: 'SPHS',     icon: '📅',  title: 'Planting Schedule',          desc: 'Optimal planting and harvest timing'},
    {id: 'COMPLETE', icon: '🗺️', title: 'Complete Farm Plan',         desc: 'All recommendations in one response'},
]

export const AREA_UNITS = [
    {value: 'ha',   label: 'Hectare (ha)'},
    {value: 'acre', label: 'Acre'},
    {value: 'm2',   label: 'Square metre (m²)'},
    {value: 'are',  label: 'Are'},
]

export const LANGUAGES = [
    {value: 'en', label: 'English'},
    {value: 'sw', label: 'Swahili'},
    {value: 'fr', label: 'French'},
]

export const CASSAVA_PRODUCE_TYPES = [
    {value: 'roots', label: 'Fresh Roots'},
    {value: 'chips', label: 'Dried Chips'},
    {value: 'flour', label: 'Cassava Flour'},
    {value: 'gari',  label: 'Gari'},
]

export const MAIZE_PRODUCE_TYPES = [
    {value: 'fresh_cob', label: 'Fresh Cob'},
    {value: 'grain',     label: 'Dry Grain'},
]

export const SWEET_POTATO_PRODUCE_TYPES = [
    {value: 'tubers', label: 'Fresh Tubers'},
    {value: 'flour',  label: 'Flour'},
]

// Used when the API has no cassava-units list
export const DEFAULT_CASSAVA_UNITS = [
    {unit_weight: 1,    label: 'ONE_KG'},
    {unit_weight: 50,   label: 'FIFTY_KG'},
    {unit_weight: 100,  label: 'HUNDRED_KG'},
    {unit_weight: 1000, label: 'THOUSAND_KG'},
]

export function cassavaUnitLabel(kg: number): string {
    if (kg === 1000) return 'per tonne'
    if (kg === 1) return 'per kg'
    return `per ${kg} kg bag`
}

export const COUNTRY_INTERCROP: Partial<Record<string, IntercropCrop>> = {
    NG: 'MAIZE',
    TZ: 'POTATO',
}

export const INITIAL_FORM_STATE = {
    scenario:              'FR'        as Scenario,
    intercropCrop:         'MAIZE'     as IntercropCrop,
    country:               '',
    fieldSize:             '1',
    areaUnit:              'ha',
    mapLat:                '',
    mapLong:               '',
    lang:                  'en',
    fieldYield:            '10',
    soilQuality:           '3',
    // Moderate: with Conservative the extra income must be 1.8× the fertilizer
    // cost, so "fertilizer doesn't pay" became the usual first answer.
    riskAttitude:          '1',
    cassavaProduceType:    'roots',
    cassavaPrice:          '',
    cassavaUnitWeight:     '1000',
    maizeProduceType:      'fresh_cob',
    maizePrice:            '',
    sweetPotatoProduceType:'tubers',
    sweetPotatoPrice:      '',
    plantingDate:          '',
    harvestDate:           '',
    plantingDateWindow:    '0',
    harvestDateWindow:     '0',
}
