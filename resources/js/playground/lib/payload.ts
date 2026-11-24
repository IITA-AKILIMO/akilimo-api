import type {FertilizerEntry, FormState} from '../types'
import {minHarvestDate, todayIso} from './dates'
import {needsSecondCrop, scenarioToFlags} from './scenario'

/** The body POSTed to /playground/compute, from the wizard's form and fertilizer list. */
export function buildPayload(form: FormState, fertilizers: FertilizerEntry[]) {
    const todayStr = todayIso()
    const defaultHarvest = minHarvestDate(todayStr)
    const fertList = fertilizers.length
        ? fertilizers
        : [{key: 'none', name: 'None', fertilizer_type: 'NONE', weight: 50, price: 0, selected: false}]
    const second = needsSecondCrop(form.scenario, form.country)

    return {
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
}
