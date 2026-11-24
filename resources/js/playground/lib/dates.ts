// Date limits the compute endpoint enforces (planting within 6 months back, harvest 7.8–15 months after planting)

const today = new Date()

const sixMonthsAgo = new Date(today)
sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6)
export const plantingMin = sixMonthsAgo.toISOString().slice(0, 10)

export const todayIso = (): string => today.toISOString().slice(0, 10)

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
