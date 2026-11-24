/**
 * Every request the playground makes to the server: the public /api/v1 reference data,
 * the health check and the signed-in /playground endpoints. Components and hooks call
 * these; nothing else in the playground calls fetch.
 */
import type {ApiResponse} from './result/types'
import type {ApiKey, CassavaUnit, Country, HistoryItem, KeyType, NewKey} from './types'
import type {PriceBand} from './lib/prices'

const JSON_HEADERS = {Accept: 'application/json'}

function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''
}

/** All rows of an API list, following its pages (the API pages at 50–100 rows). */
async function getList<T>(url: string): Promise<T[]> {
    const rows: T[] = []
    let next: string | null = url
    for (let page = 0; next && page < 10; page++) {
        const r: Response = await fetch(next, {headers: JSON_HEADERS})
        if (!r.ok) break
        const d = await r.json()
        rows.push(...((d.data ?? d) as T[]))
        next = d.links?.next ?? null
    }
    return rows
}

// ── Reference data (/api/v1, public) ──────────────────────────────────────────

export const fetchCountries = () => getList<Country>('/api/v1/countries')

export const fetchCassavaUnits = () => getList<CassavaUnit & {active?: boolean}>('/api/v1/cassava-units')

export const fetchCassavaPrices = (country: string) =>
    getList<PriceBand & {currency?: {currency_code?: string} | null}>(`/api/v1/cassava-prices/country/${country}`)

export const fetchMaizePrices = (country: string) =>
    getList<PriceBand & {produce_type?: string}>(`/api/v1/maize-prices/country/${country}`)

export const fetchPotatoPrices = (country: string) =>
    getList<PriceBand>(`/api/v1/potato-prices/country/${country}`)

export const fetchFertilizers = (country: string) =>
    getList<{fertilizer_key: string; name: string; type?: string; weight: number; available: boolean}>(
        `/api/v1/fertilizers/country/${country}?per_page=50`)

export const fetchFertilizerPrices = (country: string) =>
    getList<PriceBand & {fertilizer_key: string; active?: boolean}>(
        `/api/v1/fertilizer-prices/country/${country}?per_page=100`)

// ── Health ────────────────────────────────────────────────────────────────────

export interface HealthReport {
    status?: string
    timestamp?: string
    checks?: Record<string, Record<string, any>>
}

export const fetchHealth = async (): Promise<HealthReport> => {
    const res = await fetch('/health', {headers: JSON_HEADERS})
    return res.json()
}

// ── Compute and history (/playground, signed in) ─────────────────────────────

export type ComputeOutcome = {ok: true; data: ApiResponse} | {ok: false; message: string}

export async function computeRecommendation(payload: object): Promise<ComputeOutcome> {
    try {
        const res = await fetch('/playground/compute', {
            method:  'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken()},
            body:    JSON.stringify(payload),
        })
        const json = await res.json()
        return res.ok ? {ok: true, data: json} : {ok: false, message: json.message ?? `Error ${res.status}`}
    } catch {
        return {ok: false, message: 'Network error — please check your connection and try again.'}
    }
}

export const fetchHistory = (): Promise<HistoryItem[]> => fetch('/playground/history').then((r) => r.json())

// ── API keys (/playground/tokens, signed in) ──────────────────────────────────

export const fetchKeys = (): Promise<ApiKey[]> => fetch('/playground/tokens').then((r) => r.json())

export type CreateKeyOutcome = {ok: true; key: NewKey} | {ok: false; message: string}

export async function createKey(name: string, type: KeyType): Promise<CreateKeyOutcome> {
    // Never leave the button spinning: give up after 20 s and say so
    const controller = new AbortController()
    const timer = setTimeout(() => controller.abort(), 20000)

    try {
        const res = await fetch('/playground/tokens', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken()},
            body: JSON.stringify({name, type}),
            signal: controller.signal,
        })

        // A signed-out session is redirected to the sign-in page, which is HTML, not JSON
        const data = await res.json().catch(() => null)

        if (res.status === 419 || res.redirected || (res.ok && !data)) {
            return {ok: false, message: 'Your session has expired. Reload the page and sign in again.'}
        }
        if (!res.ok || !data?.key) {
            return {ok: false, message: data?.message ?? `Could not create the key (error ${res.status}). Please try again.`}
        }
        return {ok: true, key: data as NewKey}
    } catch (e) {
        return {
            ok: false,
            message: e instanceof DOMException && e.name === 'AbortError'
                ? 'The server did not answer in time. Please try again.'
                : 'Network error. Please try again.',
        }
    } finally {
        clearTimeout(timer)
    }
}

/** True when the server accepted the change */
export async function revokeKey(id: number): Promise<boolean> {
    const res = await fetch(`/playground/tokens/${id}/revoke`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json'},
    })
    return res.ok
}

export async function deleteKey(id: number): Promise<boolean> {
    const res = await fetch(`/playground/tokens/${id}`, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json'},
    })
    return res.ok
}
