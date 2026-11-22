import {useEffect, useState} from 'react'
import RecommendationResult, {type ApiResponse} from './RecommendationResult'

// plumber_response is the full ApiResponse envelope
interface HistoryFlags {
    fr: boolean | null
    ic: boolean | null
    pp: boolean | null
    sph: boolean | null
    spp: boolean | null
}

interface HistoryItem {
    id: number
    request_id: string
    country_code: string | null
    use_case: string | null
    flags: HistoryFlags
    duration_ms: number | null
    created_at: string | null
    result: ApiResponse | null
}

const SCENARIO_COLORS: Record<string, string> = {
    FR: '#166534',
    IC: '#92400e',
    PP: '#1e40af',
    SPHS: '#6b21a8',
    PDC: '#0f766e',
    NA: '#374151',
}

function timeAgo(iso: string): string {
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000)
    if (diff < 60) return `${diff}s ago`
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
    return `${Math.floor(diff / 86400)}d ago`
}

function FlagPills({flags}: {flags: HistoryFlags}) {
    const active = Object.entries(flags)
        .filter(([, v]) => v)
        .map(([k]) => k.toUpperCase())
    if (!active.length) return null
    return (
        <span className="hist-flags">
            {active.map((f) => (
                <span key={f} className="hist-flag">{f}</span>
            ))}
        </span>
    )
}

export default function HistoryPanel() {
    const [items, setItems] = useState<HistoryItem[]>([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)
    const [expanded, setExpanded] = useState<number | null>(null)

    function load() {
        setLoading(true)
        setError(null)
        fetch('/playground/history')
            .then((r) => r.json())
            .then((d: HistoryItem[]) => setItems(d))
            .catch(() => setError('Could not load history. Please try again.'))
            .finally(() => setLoading(false))
    }

    useEffect(() => { load() }, [])

    if (loading) {
        return (
            <div className="hist-state">
                <div className="fert-spinner"/>
                Loading history…
            </div>
        )
    }

    if (error) {
        return (
            <div className="hist-state hist-state--error">
                <span>{error}</span>
                <button className="btn btn-ghost" style={{marginTop: '0.75rem'}} onClick={load}>Retry</button>
            </div>
        )
    }

    if (!items.length) {
        return (
            <div className="hist-state">
                <div className="hist-empty-icon">📋</div>
                <p>No playground requests yet.</p>
                <p style={{fontSize: '0.875rem', color: 'var(--text-muted)'}}>
                    Use the <strong>Playground</strong> tab to compute your first recommendation.
                </p>
            </div>
        )
    }

    return (
        <div className="hist-wrap">
            <div className="hist-header">
                <span className="hist-count">{items.length} recent request{items.length !== 1 ? 's' : ''}</span>
                <button className="btn btn-ghost btn--sm" onClick={load}>↻ Refresh</button>
            </div>

            <div className="hist-list">
                {items.map((item) => {
                    const isOpen = expanded === item.id
                    const color = SCENARIO_COLORS[item.use_case ?? 'NA'] ?? SCENARIO_COLORS.NA
                    const recType = item.result?.data?.rec_type ?? item.use_case ?? '—'
                    const recommendation = item.result?.data?.recommendation ?? ''
                    const snippet = recommendation
                        ? recommendation.slice(0, 120) + (recommendation.length > 120 ? '…' : '')
                        : null

                    return (
                        <div key={item.id} className={`hist-row${isOpen ? ' hist-row--open' : ''}`}>
                            <button
                                className="hist-row-summary"
                                onClick={() => setExpanded(isOpen ? null : item.id)}
                                aria-expanded={isOpen}
                            >
                                <span className="hist-badge" style={{'--badge-color': color} as React.CSSProperties}>
                                    {recType}
                                </span>

                                <span className="hist-meta">
                                    <span className="hist-country">{item.country_code ?? '—'}</span>
                                    <FlagPills flags={item.flags}/>
                                </span>

                                <span className="hist-right">
                                    {item.duration_ms != null && (
                                        <span className="hist-duration">{(item.duration_ms / 1000).toFixed(1)}s</span>
                                    )}
                                    {item.created_at && (
                                        <span className="hist-time">{timeAgo(item.created_at)}</span>
                                    )}
                                    <span className={`hist-chevron${isOpen ? ' hist-chevron--open' : ''}`}>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"
                                             strokeLinejoin="round">
                                            <polyline points="6 9 12 15 18 9"/>
                                        </svg>
                                    </span>
                                </span>
                            </button>

                            {snippet && !isOpen && (
                                <p className="hist-snippet">{snippet}</p>
                            )}

                            {isOpen && (
                                <div className="hist-detail">
                                    {item.result ? (
                                        <RecommendationResult
                                            result={{
                                                ...item.result,
                                                request_id: item.result.request_id ?? item.request_id,
                                            }}
                                            country={item.country_code ?? ''}
                                        />
                                    ) : (
                                        <p className="hist-no-result">No result data stored for this request.</p>
                                    )}
                                    <p className="hist-request-id">
                                        Request ID: <code>{item.request_id}</code>
                                    </p>
                                </div>
                            )}
                        </div>
                    )
                })}
            </div>
        </div>
    )
}
