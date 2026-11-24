import {useEffect, useState} from 'react'
import {fetchHistory} from '../api'
import Icon from '../components/Icon'
import Spinner from '../components/Spinner'
import {timeAgo} from '../lib/format'
import RecommendationResult from '../result/RecommendationResult'
import type {HistoryFlags, HistoryItem} from '../types'

// A Bootstrap colour per use case
const SCENARIO_TONES: Record<string, string> = {
    FR: 'success',
    IC: 'warning',
    PP: 'primary',
    SPHS: 'info',
    PDC: 'secondary',
    NA: 'dark',
}

function FlagPills({flags}: Readonly<{flags: HistoryFlags}>) {
    const active = Object.entries(flags)
        .filter(([, v]) => v)
        .map(([k]) => k.toUpperCase())
    if (!active.length) return null
    return (
        <span className="d-inline-flex gap-1">
            {active.map((f) => <span key={f} className="badge bg-body-secondary text-body border">{f}</span>)}
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
        fetchHistory()
            .then((d) => setItems(d))
            .catch(() => setError('Could not load history. Please try again.'))
            .finally(() => setLoading(false))
    }

    useEffect(() => { load() }, [])

    if (loading) {
        return <div className="card"><div className="card-body text-center text-body-secondary py-5"><Spinner text="Loading history…"/></div></div>
    }

    if (error) {
        return (
            <div className="alert alert-danger d-flex justify-content-between align-items-center" role="alert">
                <span>{error}</span>
                <button type="button" className="btn btn-sm btn-outline-danger" onClick={load}>Retry</button>
            </div>
        )
    }

    if (!items.length) {
        return (
            <div className="card">
                <div className="card-body text-center py-5">
                    <div className="fs-1" aria-hidden="true">📋</div>
                    <p className="mb-1">No playground requests yet.</p>
                    <p className="small text-body-secondary mb-0">
                        Use the <strong>Playground</strong> tab to compute your first recommendation.
                    </p>
                </div>
            </div>
        )
    }

    return (
        <div>
            <div className="d-flex justify-content-between align-items-center mb-2">
                <span className="small text-body-secondary">{items.length} recent request{items.length !== 1 ? 's' : ''}</span>
                <button type="button" className="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" onClick={load}>
                    <Icon name="refresh" size={13}/> Refresh
                </button>
            </div>

            <div className="list-group">
                {items.map((item) => {
                    const isOpen = expanded === item.id
                    const tone = SCENARIO_TONES[item.use_case ?? 'NA'] ?? SCENARIO_TONES.NA
                    const recType = item.result?.data?.rec_type ?? item.use_case ?? '—'
                    const recommendation = item.result?.data?.recommendation ?? ''
                    const snippet = recommendation
                        ? recommendation.slice(0, 120) + (recommendation.length > 120 ? '…' : '')
                        : null

                    return (
                        <div key={item.id} className="list-group-item p-0">
                            <button type="button" className="btn w-100 text-start d-flex flex-wrap align-items-center gap-2 p-3 border-0"
                                    onClick={() => setExpanded(isOpen ? null : item.id)} aria-expanded={isOpen}>
                                <span className={`badge text-bg-${tone}`}>{recType}</span>
                                <span className="d-inline-flex align-items-center gap-2">
                                    <span className="fw-semibold">{item.country_code ?? '—'}</span>
                                    <FlagPills flags={item.flags}/>
                                </span>
                                <span className="ms-auto d-inline-flex align-items-center gap-3 small text-body-secondary">
                                    {item.duration_ms != null && <span>{(item.duration_ms / 1000).toFixed(1)}s</span>}
                                    {item.created_at && <span>{timeAgo(item.created_at)}</span>}
                                    <Icon name={isOpen ? 'up' : 'down'} size={12}/>
                                </span>
                            </button>

                            {snippet && !isOpen && <p className="px-3 pb-3 mb-0 small text-body-secondary">{snippet}</p>}

                            {isOpen && (
                                <div className="px-3 pb-3">
                                    {item.result ? (
                                        <RecommendationResult
                                            result={{...item.result, request_id: item.result.request_id ?? item.request_id}}
                                            country={item.country_code ?? ''}
                                        />
                                    ) : (
                                        <p className="small text-body-secondary">No result data stored for this request.</p>
                                    )}
                                    <p className="small text-body-secondary mt-2 mb-0">
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
