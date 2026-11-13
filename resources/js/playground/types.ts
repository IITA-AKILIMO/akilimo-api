export type Scenario = 'FR' | 'IC' | 'PP' | 'SPHS' | 'COMPLETE'
export type IntercropCrop = 'MAIZE' | 'POTATO'
export type Step = 1 | 2 | 3 | 4 | 5

export interface Country {
    id: number
    code: string
    name: string
}

export interface Fertilizer {
    id: number
    name: string
    fertilizer_key: string
    fertilizer_type?: string
    weight: number
    country: string
    available: boolean
}

export interface CassavaUnit {
    unit_weight: number
    label: string
}

/** What the API's price lists say for the chosen country, used to prefill prices. */
export interface PriceHints {
    currency: string | null
    /** Middle band of the cassava price list, per tonne of fresh roots */
    cassavaPerTonne: number | null
    maizeCob: number | null
    maizeGrain: number | null
    sweetPotatoPerTonne: number | null
}

export interface FertilizerEntry {
    key: string
    name: string
    fertilizer_type: string
    weight: number
    price: number
    selected: boolean
}

export interface FormState {
    scenario: Scenario
    intercropCrop: IntercropCrop
    country: string
    fieldSize: string
    areaUnit: string
    mapLat: string
    mapLong: string
    lang: string
    fieldYield: string
    soilQuality: string
    riskAttitude: string
    cassavaProduceType: string
    // Selling prices in local currency; cassava per unit of cassavaUnitWeight kg
    cassavaPrice: string
    cassavaUnitWeight: string
    maizeProduceType: string
    maizePrice: string
    sweetPotatoProduceType: string
    sweetPotatoPrice: string
    plantingDate: string
    harvestDate: string
    plantingDateWindow: string
    harvestDateWindow: string
}
