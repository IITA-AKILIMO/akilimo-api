import { Link, router, usePage } from '@inertiajs/react'
import 'bootstrap/js/dist/collapse'
import { useCallback, useEffect, useRef, useState, type ReactNode } from 'react'
import AdminLayout from '../layouts/AdminLayout'

interface DailyRequest {
    date: string
    count: number
}

interface UseCaseStat {
    use_case: string
    count: number
}

interface DashboardProps {
    chartDays: number
    stats: {
        totalRequests: number
        totalUsers: number
        activeKeys: number
        totalFertilizers: number
        totalCountries: number
    }
    charts: {
        dailyRequests: DailyRequest[]
        byUseCase: UseCaseStat[]
        byCountry: { country_code: string; count: number }[]
        byGender: { female: number; male: number; not_recorded: number }
    }
    metrics: {
        medianResponseMs: number | null
        p95ResponseMs: number | null
        timedRequests: number
        successRate: number | null
        answeredRequests: number
        failedRequests: number
        unansweredRequests: number
        requestsLastDays: number
        staffRequests: number
        uniqueDevices: number
        periodDays: number
    }
    feedback: {
        responses: number
        responsesLastDays: number
        avgSatisfaction: number | null
        ratedResponses: number
        distribution: Record<string, number>
        lastResponseAt: string | null
    }
    // Admins only
    failures?: {
        id: number
        country_code: string | null
        use_case: string | null
        status_code: number | null
        message: string
        created_at: string
    }[]
    freshness?: {
        staleAfterDays: number
        prices: { label: string; href: string; rows: number; last_updated: string | null; stale: boolean }[]
        coverage: { country_code: string; fetched_at: string | null }[]
    }
    recentRequests: {
        id: number
        request_id: string | number
        device_token: string | null
        duration_ms: number | null
        created_at: string
        use_case?: string | null
        country_code?: string | null
    }[]
}

const CHART_PERIODS = [7, 14, 30, 90]
const METRIC_PERIODS = [7, 14, 30]

function navigate(chartDays: number, metricsDays: number, refresh = false) {
    const params: Record<string, string | number> = { chart_days: chartDays, metrics_days: metricsDays }
    if (refresh) params.refresh = 1
    router.get('/admin', params, { preserveScroll: true, replace: true })
}

function formatMs(ms: number | null): string {
    if (ms === null) return '—'
    return ms >= 1000 ? `${(ms / 1000).toFixed(1)}s` : `${ms}ms`
}

function formatDate(iso: string | null): string {
    return iso ? new Date(iso).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }) : 'Never'
}

function MetricCard({ label, value, valueClass = '', note }: { label: string; value: string; valueClass?: string; note: string }) {
    return (
        <div className="col-sm-6 col-xl-3">
            <div className="card shadow-sm h-100">
                <div className="card-body">
                    <p className="text-muted small fw-medium text-uppercase mb-1">{label}</p>
                    <p className={`fs-3 fw-bold mb-1 ${valueClass}`}>{value}</p>
                    <p className="text-muted small mb-0">{note}</p>
                </div>
            </div>
        </div>
    )
}

const PANELS_KEY = 'dashboard-panels'

function readPanels(): Record<string, boolean> {
    try {
        return JSON.parse(localStorage.getItem(PANELS_KEY) ?? '{}') ?? {}
    } catch {
        return {}
    }
}

interface PanelProps {
    id: string
    title: ReactNode
    extra?: ReactNode
    /** Stretch to the row's height (cards side by side). */
    fill?: boolean
    defaultOpen?: boolean
    children: ReactNode
}

/**
 * A card whose body folds away (Bootstrap Collapse). Each panel's open or
 * closed state is remembered in this browser.
 */
function Panel({ id, title, extra, fill = true, defaultOpen = true, children }: PanelProps) {
    // Only the first render decides; Bootstrap owns the classes after that.
    const [open] = useState(() => readPanels()[id] ?? defaultOpen)
    const body = useRef<HTMLDivElement>(null)
    const bodyId = `panel-${id}`

    useEffect(() => {
        const el = body.current
        if (!el) return
        const save = (e: Event) => {
            if (e.target !== el) return
            try {
                localStorage.setItem(PANELS_KEY, JSON.stringify({ ...readPanels(), [id]: e.type === 'shown.bs.collapse' }))
            } catch {
                // private mode: the choice just isn't remembered
            }
        }
        el.addEventListener('shown.bs.collapse', save)
        el.addEventListener('hidden.bs.collapse', save)
        return () => {
            el.removeEventListener('shown.bs.collapse', save)
            el.removeEventListener('hidden.bs.collapse', save)
        }
    }, [id])

    return (
        <section className={`card shadow-sm dashboard-panel${fill ? ' h-100' : ''}`} aria-labelledby={`${bodyId}-title`}>
            <div className="card-header bg-white py-3 d-flex align-items-center gap-2">
                <h2 className="h6 mb-0 fw-semibold flex-grow-1">
                    <button
                        id={`${bodyId}-title`}
                        type="button"
                        className={`panel-toggle${open ? '' : ' collapsed'}`}
                        data-bs-toggle="collapse"
                        data-bs-target={`#${bodyId}`}
                        aria-expanded={open}
                        aria-controls={bodyId}
                    >
                        <svg className="panel-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                        {title}
                    </button>
                </h2>
                {extra}
            </div>
            <div id={bodyId} ref={body} className={`collapse${open ? ' show' : ''}`}>
                {children}
            </div>
        </section>
    )
}

/** A labelled horizontal bar, as in the use case card. */
function BarRow({ label, count, total }: { label: string; count: number; total: number }) {
    const pct = total > 0 ? Math.round((count / total) * 100) : 0
    return (
        <div>
            <div className="d-flex justify-content-between mb-1">
                <span className="small text-truncate" style={{ maxWidth: '60%' }}>{label}</span>
                <span className="small text-muted">{count.toLocaleString()} ({pct}%)</span>
            </div>
            <div className="progress bg-body-secondary" style={{ height: 5, borderRadius: 100 }}>
                <div
                    className="progress-bar"
                    style={{ width: `${pct}%`, background: 'var(--brand-gradient)', borderRadius: 100 }}
                    role="progressbar"
                    aria-label={label}
                    aria-valuenow={pct}
                    aria-valuemin={0}
                    aria-valuemax={100}
                />
            </div>
        </div>
    )
}

function Tooltip({ text, children }: { text: string; children: React.ReactNode }) {
    const [visible, setVisible] = useState(false)
    return (
        <span
            style={{ position: 'relative', display: 'inline-block' }}
            onMouseEnter={() => setVisible(true)}
            onMouseLeave={() => setVisible(false)}
        >
            {children}
            {visible && (
                <span
                    style={{
                        position: 'absolute',
                        bottom: '110%',
                        left: '50%',
                        transform: 'translateX(-50%)',
                        background: 'rgba(0,0,0,0.8)',
                        color: '#fff',
                        padding: '3px 8px',
                        borderRadius: 4,
                        fontSize: 12,
                        whiteSpace: 'nowrap',
                        pointerEvents: 'none',
                        zIndex: 10,
                    }}
                >
                    {text}
                </span>
            )}
        </span>
    )
}

export default function Dashboard() {
    const props = usePage().props as unknown as DashboardProps
    const data = props

    const chartDays = data.chartDays ?? 30
    const metricsDays = data.metrics?.periodDays ?? 7
    const [refreshing, setRefreshing] = useState(false)

    const handleRefresh = useCallback(() => {
        setRefreshing(true)
        router.get(
            '/admin',
            { chart_days: chartDays, metrics_days: metricsDays, refresh: 1 },
            {
                preserveScroll: true,
                replace: true,
                onFinish: () => setRefreshing(false),
            },
        )
    }, [chartDays, metricsDays])

    const statCards = data
        ? [
              { label: 'Total Requests', value: data.stats.totalRequests.toLocaleString(), description: 'All time compute requests', color: 'primary', accent: 'primary' },
              { label: 'Active Users', value: data.stats.totalUsers.toLocaleString(), description: 'Registered users', color: 'success', accent: 'success' },
              { label: 'Active API Keys', value: data.stats.activeKeys.toLocaleString(), description: 'Currently valid keys', color: 'info', accent: 'info' },
              { label: 'Countries', value: data.stats.totalCountries.toString(), description: 'Supported countries', color: 'warning', accent: 'warning' },
          ]
        : [
              { label: 'Total Requests', value: '—', description: 'All time compute requests', color: 'primary', accent: 'primary' },
              { label: 'Active Users', value: '—', description: 'Registered users', color: 'success', accent: 'success' },
              { label: 'Active API Keys', value: '—', description: 'Currently valid keys', color: 'info', accent: 'info' },
              { label: 'Countries', value: '—', description: 'Supported countries', color: 'warning', accent: 'warning' },
          ]

    const maxDaily = data?.charts.dailyRequests?.length ? Math.max(...data.charts.dailyRequests.map((d) => d.count)) : 0

    const totalUseCases = data?.charts.byUseCase?.reduce((sum, r) => sum + r.count, 0) ?? 0

    const successRate = data?.metrics.successRate ?? null
    const successColor =
        successRate === null ? 'text-body-secondary' : successRate >= 95 ? 'text-success' : successRate >= 80 ? 'text-warning' : 'text-danger'

    const countryTotal = data?.charts.byCountry?.reduce((sum, r) => sum + r.count, 0) ?? 0
    const gender = data?.charts.byGender ?? { female: 0, male: 0, not_recorded: 0 }
    const genderTotal = gender.female + gender.male + gender.not_recorded
    const feedback = data?.feedback
    const stalePrices = data?.freshness?.prices.filter((p) => p.stale).length ?? 0

    return (
        <AdminLayout title="Dashboard">
            {/* Filter toolbar */}
            <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div className="d-flex flex-wrap align-items-center gap-3">
                    {/* Chart period */}
                    <div className="d-flex align-items-center gap-2">
                        <span className="text-muted small fw-medium">Chart:</span>
                        <div className="btn-group btn-group-sm">
                            {CHART_PERIODS.map((d) => (
                                <button
                                    key={d}
                                    type="button"
                                    className={`btn ${chartDays === d ? 'btn-primary' : 'btn-outline-secondary'}`}
                                    onClick={() => navigate(d, metricsDays)}
                                >
                                    {d}d
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Metrics period */}
                    <div className="d-flex align-items-center gap-2">
                        <span className="text-muted small fw-medium">Metrics:</span>
                        <div className="btn-group btn-group-sm">
                            {METRIC_PERIODS.map((d) => (
                                <button
                                    key={d}
                                    type="button"
                                    className={`btn ${metricsDays === d ? 'btn-info' : 'btn-outline-secondary'}`}
                                    onClick={() => navigate(chartDays, d)}
                                >
                                    {d}d
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Refresh */}
                <button
                    type="button"
                    className="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                    onClick={handleRefresh}
                    disabled={refreshing}
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth={2}
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        style={{ animation: refreshing ? 'spin 1s linear infinite' : undefined }}
                    >
                        <path d="M21 2v6h-6M3 12a9 9 0 0 1 15-6.7L21 8M3 22v-6h6M21 12a9 9 0 0 1-15 6.7L3 16" />
                    </svg>
                    {refreshing ? 'Refreshing…' : 'Refresh'}
                </button>
            </div>

            {/* Stats Cards */}
            <div className="row g-4 mb-4">
                {statCards.map((card) => (
                    <div key={card.label} className="col-sm-6 col-xl-3">
                        <div className={`card shadow-sm h-100 stat-card stat-${card.accent}`}>
                            <div className="card-body">
                                <p className="text-muted small fw-medium text-uppercase mb-1" style={{ letterSpacing: '0.04em', fontSize: '0.72rem' }}>{card.label}</p>
                                <p className={`fw-bold text-${card.color} mb-1`} style={{ fontSize: '1.875rem', letterSpacing: '-0.02em', lineHeight: 1.1 }}>{card.value}</p>
                                <p className="text-muted mb-0" style={{ fontSize: '0.8125rem' }}>{card.description}</p>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Metrics Row */}
            {data && (
                <>
                    <h2 className="h6 text-body-secondary fw-semibold mb-3">Last {metricsDays} days</h2>
                    <div className="row g-4 mb-4">
                        <MetricCard
                            label="Requests"
                            value={data.metrics.requestsLastDays.toLocaleString()}
                            valueClass="text-primary"
                            note={data.metrics.staffRequests > 0
                                ? `Plus ${data.metrics.staffRequests.toLocaleString()} from staff and testing`
                                : 'From farmers and partners'}
                        />
                        <MetricCard
                            label="Devices reached"
                            value={data.metrics.uniqueDevices.toLocaleString()}
                            note="Distinct phones and clients"
                        />
                        <MetricCard
                            label="Success rate"
                            value={successRate === null ? '—' : `${successRate}%`}
                            valueClass={successColor}
                            note={(data.metrics.answeredRequests > 0
                                ? `${data.metrics.failedRequests.toLocaleString()} of ${data.metrics.answeredRequests.toLocaleString()} failed`
                                : 'No computed requests')
                                + (data.metrics.unansweredRequests > 0
                                    ? ` · ${data.metrics.unansweredRequests.toLocaleString()} with no answer recorded`
                                    : '')}
                        />
                        <MetricCard
                            label="Response time"
                            value={formatMs(data.metrics.medianResponseMs)}
                            note={data.metrics.timedRequests > 0
                                ? `Median · 95% within ${formatMs(data.metrics.p95ResponseMs)}`
                                : 'No timed requests'}
                        />
                    </div>
                </>
            )}

            <div className="row g-4 mb-4">
                {/* Daily Requests Bar Chart */}
                <div className="col-lg-8">
                    <Panel id="daily-requests" title={<>Requests (Last {chartDays} Days)</>} extra={<>{data?.charts.dailyRequests?.length > 0 && ( <span className="badge bg-light text-secondary border"> peak {maxDaily.toLocaleString()} </span> )}</>}>
                        <div className="card-body">
                            {data?.charts.dailyRequests?.length ? (
                                <>
                                    <div className="d-flex align-items-end gap-1" style={{ height: 200 }}>
                                        {data.charts.dailyRequests.map((day, i) => (
                                            <Tooltip key={i} text={`${day.date}: ${day.count.toLocaleString()}`}>
                                                <div
                                                    className="flex-fill rounded-top"
                                                    style={{
                                                        height: `${maxDaily ? (day.count / maxDaily) * 100 : 0}%`,
                                                        minHeight: day.count > 0 ? '4px' : '0',
                                                        cursor: 'default',
                                                        background: 'var(--brand-gradient)',
                                                        transition: 'opacity 0.15s',
                                                    }}
                                                    onMouseEnter={(e) => ((e.currentTarget as HTMLElement).style.opacity = '0.65')}
                                                    onMouseLeave={(e) => ((e.currentTarget as HTMLElement).style.opacity = '1')}
                                                />
                                            </Tooltip>
                                        ))}
                                    </div>
                                    <div className="d-flex justify-content-between mt-2">
                                        <span className="small text-muted">{data.charts.dailyRequests[0]?.date}</span>
                                        <span className="small text-muted">
                                            {data.charts.dailyRequests[data.charts.dailyRequests.length - 1]?.date}
                                        </span>
                                    </div>
                                </>
                            ) : (
                                <div className="text-center text-muted py-5">No request data available</div>
                            )}
                        </div>
                    </Panel>
                </div>

                {/* Requests by Use Case */}
                <div className="col-lg-4">
                    <Panel id="use-cases" title={<>By Use Case</>} extra={<>{totalUseCases > 0 && ( <span className="badge bg-light text-secondary border">{totalUseCases.toLocaleString()} total</span> )}</>}>
                        <div className="card-body">
                            {data?.charts.byUseCase?.length ? (
                                <div className="d-flex flex-column gap-3">
                                    {data.charts.byUseCase.map((item, i) => (
                                        <BarRow key={i} label={item.use_case || 'unknown'} count={item.count} total={totalUseCases} />
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center text-muted py-5">No use case data</div>
                            )}
                        </div>
                    </Panel>
                </div>
            </div>

            {/* Reach and feedback */}
            {data && (
                <div className="row g-4 mb-4">
                    <div className="col-lg-4">
                        <Panel id="countries" title={<>By Country</>} extra={<><span className="badge bg-light text-secondary border">{chartDays}d</span></>}>
                            <div className="card-body">
                                {data.charts.byCountry?.length ? (
                                    <div className="d-flex flex-column gap-3">
                                        {data.charts.byCountry.map((c) => (
                                            <BarRow key={c.country_code} label={c.country_code} count={c.count} total={countryTotal} />
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center text-muted py-5">No requests in this period</div>
                                )}
                            </div>
                        </Panel>
                    </div>

                    <div className="col-lg-4">
                        <Panel id="gender" title={<>Requests by Gender</>} extra={<><span className="badge bg-light text-secondary border">{chartDays}d</span></>}>
                            <div className="card-body">
                                {genderTotal > 0 ? (
                                    <div className="d-flex flex-column gap-3">
                                        <BarRow label="Female" count={gender.female} total={genderTotal} />
                                        <BarRow label="Male" count={gender.male} total={genderTotal} />
                                        <BarRow label="Not recorded" count={gender.not_recorded} total={genderTotal} />
                                        <p className="small text-muted mb-0">
                                            As given in the request; "not recorded" includes the MCP chat's anonymous requests.
                                        </p>
                                    </div>
                                ) : (
                                    <div className="text-center text-muted py-5">No requests in this period</div>
                                )}
                            </div>
                        </Panel>
                    </div>

                    <div className="col-lg-4">
                        <Panel id="feedback" title={<>App Feedback</>} extra={<><span className="badge bg-light text-secondary border">{feedback?.responses.toLocaleString() ?? 0} total</span></>}>
                            <div className="card-body">
                                {feedback && feedback.ratedResponses > 0 ? (
                                    <>
                                        <div className="d-flex align-items-baseline gap-2 mb-1">
                                            <span className="fs-3 fw-bold">{feedback.avgSatisfaction?.toFixed(1)}</span>
                                            <span className="text-muted small">/ 5 average satisfaction</span>
                                        </div>
                                        <p className="small text-muted mb-3">
                                            {feedback.ratedResponses.toLocaleString()} ratings ·{' '}
                                            {feedback.responsesLastDays > 0
                                                ? `${feedback.responsesLastDays.toLocaleString()} in the last ${metricsDays} days`
                                                : `last one ${formatDate(feedback.lastResponseAt)}`}
                                        </p>
                                        <div className="d-flex flex-column gap-2">
                                            {[5, 4, 3, 2, 1].map((r) => (
                                                <BarRow key={r} label={`${r} ★`} count={feedback.distribution[r] ?? 0} total={feedback.ratedResponses} />
                                            ))}
                                        </div>
                                    </>
                                ) : (
                                    <div className="text-center text-muted py-5">No feedback yet</div>
                                )}
                            </div>
                        </Panel>
                    </div>
                </div>
            )}

            {/* Admins only: what needs attention */}
            {(data?.failures || data?.freshness) && (
                <div className="row g-4 mb-4">
                    {data.failures && (
                        <div className="col-lg-7">
                            <Panel id="failures" title={<>Recent Failures</>} extra={<><Link href="/admin/requests" className="small">Request log</Link></>}>
                                <div className="card-body p-0">
                                    {data.failures.length ? (
                                        <ul className="list-group list-group-flush">
                                            {data.failures.map((f) => (
                                                <li key={f.id} className="list-group-item px-3 py-2">
                                                    <div className="d-flex flex-wrap align-items-center gap-2 small mb-1">
                                                        <Link href={`/admin/requests/${f.id}`} className="fw-medium">#{f.id}</Link>
                                                        {f.country_code && <span className="badge bg-light text-dark border">{f.country_code}</span>}
                                                        {f.use_case && <span className="badge bg-light text-dark border">{f.use_case}</span>}
                                                        {f.status_code && <span className="badge bg-danger-subtle text-danger-emphasis">{f.status_code}</span>}
                                                        <span className="text-muted ms-auto">{new Date(f.created_at).toLocaleString()}</span>
                                                    </div>
                                                    <div className="small text-body-secondary text-break">{f.message}</div>
                                                </li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <div className="text-center text-muted py-4">No failed requests</div>
                                    )}
                                </div>
                            </Panel>
                        </div>
                    )}

                    {data.freshness && (
                        <div className="col-lg-5">
                            <Panel id="freshness" title={<>Data Freshness</>} extra={<>{stalePrices > 0 && ( <span className="badge bg-warning-subtle text-warning-emphasis">{stalePrices} stale</span> )}</>}>
                                <div className="card-body p-0">
                                    <ul className="list-group list-group-flush">
                                        {data.freshness.prices.map((p) => (
                                            <li key={p.href} className="list-group-item px-3 py-2 d-flex align-items-center gap-2 small">
                                                <Link href={p.href} className="fw-medium">{p.label}</Link>
                                                <span className="text-muted">{p.rows.toLocaleString()} rows</span>
                                                <span className={`ms-auto ${p.stale ? 'text-warning-emphasis fw-medium' : 'text-muted'}`}>
                                                    {p.last_updated ? formatDate(p.last_updated) : 'No date'}
                                                </span>
                                            </li>
                                        ))}
                                        <li className="list-group-item px-3 py-2 small">
                                            <div className="fw-medium mb-1">Supported areas</div>
                                            <div className="d-flex flex-wrap gap-1">
                                                {data.freshness.coverage.map((c) => (
                                                    <span
                                                        key={c.country_code}
                                                        className={`badge ${c.fetched_at ? 'bg-light text-dark border' : 'bg-warning-subtle text-warning-emphasis'}`}
                                                        title={c.fetched_at ? `Synced ${formatDate(c.fetched_at)}` : 'No supported area synced'}
                                                    >
                                                        {c.country_code} {c.fetched_at ? '✓' : '— missing'}
                                                    </span>
                                                ))}
                                            </div>
                                        </li>
                                    </ul>
                                    <p className="small text-muted px-3 py-2 mb-0 border-top">
                                        Marked stale after {data.freshness.staleAfterDays} days.
                                    </p>
                                </div>
                            </Panel>
                        </div>
                    )}
                </div>
            )}

            {/* Recent Requests Table */}
            <Panel id="recent-requests" title={<>Recent Requests</>} fill={false} defaultOpen={false}>
                <div className="card-body p-0">
                    {data?.recentRequests?.length ? (
                        <div className="table-responsive">
                            <table className="table table-hover mb-0">
                                <thead className="table-light">
                                    <tr>
                                        <th className="px-3 py-2">ID</th>
                                        <th className="px-3 py-2">Device</th>
                                        <th className="px-3 py-2">Country</th>
                                        <th className="px-3 py-2">Use Case</th>
                                        <th className="px-3 py-2">Request Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.recentRequests.map((req) => (
                                        <tr key={req.id}>
                                            <td className="px-3 py-2 text-muted small">{req.id}</td>
                                            <td className="px-3 py-2">
                                                <code className="small">{req.device_token || '—'}</code>
                                            </td>
                                            <td className="px-3 py-2">
                                                {req.country_code ? (
                                                    <span className="badge bg-light text-dark border">{req.country_code}</span>
                                                ) : (
                                                    '—'
                                                )}
                                            </td>
                                            <td className="px-3 py-2 small">{req.use_case || '—'}</td>
                                            <td className="px-3 py-2 small text-muted">
                                                {req.created_at ? new Date(req.created_at).toLocaleString() : '—'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="text-center text-muted py-4">No recent requests</div>
                    )}
                </div>
            </Panel>

            <style>{`
                @keyframes spin { to { transform: rotate(360deg); } }
            `}</style>
        </AdminLayout>
    )
}
