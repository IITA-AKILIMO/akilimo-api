const COUNTRY_CURRENCY: Record<string, string> = {
    NG: 'NGN', TZ: 'TZS', KE: 'KES', RW: 'RWF', GH: 'GHS', BI: 'BIF',
}

export function fmt(value: number, country: string): string {
    const currency = COUNTRY_CURRENCY[country]
    if (!currency) return value.toLocaleString(undefined, {maximumFractionDigits: 0})
    try {
        return new Intl.NumberFormat('en', {
            style: 'currency', currency,
            maximumFractionDigits: 0, minimumFractionDigits: 0,
        }).format(value)
    } catch {
        return value.toLocaleString(undefined, {maximumFractionDigits: 0})
    }
}

export function fmtDate(d: string): string {
    try {
        return new Date(d + 'T00:00:00').toLocaleDateString('en', {
            day: 'numeric', month: 'short', year: 'numeric',
        })
    } catch {
        return d
    }
}

export function pctChange(from: number, to: number): {value: string; positive: boolean} | null {
    if (!from || from === 0) return null
    const p = Math.round(((to - from) / from) * 100)
    return {value: p >= 0 ? `+${p}%` : `${p}%`, positive: p >= 0}
}

export function weekOffset(w: number): string {
    if (w === 0) return 'as requested'
    return w > 0 ? `${w} wk later` : `${Math.abs(w)} wk earlier`
}

export function methodLabel(m: string): string {
    if (!m || m === 'N/A' || m === 'NA') return '—'
    return m.charAt(0).toUpperCase() + m.slice(1).toLowerCase()
}
