import {fetchHealth, type HealthReport} from './api'

// The system health page (/playground/health): one Bootstrap card per service from GET /health.

type Check = Record<string, any>

const SERVICE_LABELS: Record<string, string> = {
    'database': 'Database',
    'redis': 'Redis',
    'cache': 'Cache',
    'storage': 'File Storage',
    'queue': 'Queue',
    'mail': 'Mail',
    'disk-space': 'Disk Space',
    'migrations': 'Migrations',
    'env-config': 'Environment',
    'php-extensions': 'PHP Extensions',
    'akilimo-compute': 'Akilimo Compute',
}

const escapeHtml = (v: unknown) =>
    String(v).replace(/[&<>"']/g, (c) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c] as string))

function formatBytes(bytes: number): string {
    if (!bytes || Number.isNaN(bytes)) return '—'
    const gb = bytes / 1073741824
    if (gb >= 1) return gb.toFixed(1) + ' GB'
    return (bytes / 1048576).toFixed(0) + ' MB'
}

function detailRows(key: string, data: Check): [string, unknown][] {
    const rows: [string, unknown][] = []
    const skip = new Set(['status', 'error'])

    if (key === 'database') {
        rows.push(['Database', data.database || '—'], ['Driver', data.database_type || '—'], ['Tables', data.total_tables ?? '—'])
    } else if (key === 'redis') {
        rows.push(['Version', data.version || '—'], ['Used mem', data.memory?.used || '—'], ['Peak mem', data.memory?.peak || '—'])
    } else if (key === 'cache') {
        rows.push(['Driver', data.driver || '—'])
    } else if (key === 'storage') {
        rows.push(['Disk', data.default_disk || '—'])
        if (data.root_path) rows.push(['Root', data.root_path])
    } else if (key === 'queue') {
        rows.push(['Connection', data.default_connection || '—'])
    } else if (key === 'mail') {
        const t = data.transport
        if (t && typeof t === 'object') {
            rows.push(['Transport', String(t.class || t.constructor?.name || 'unknown').split('\\').pop()])
        } else {
            rows.push(['Transport', String(t || '—')])
        }
    } else if (key === 'disk-space') {
        rows.push(['Used', data.used_percentage || '—'], ['Free', formatBytes(data.free_space)], ['Total', formatBytes(data.total_space)])
    } else if (key === 'migrations') {
        rows.push(['Applied', data.total_migrations ?? '—'])
    } else if (key === 'env-config') {
        rows.push(['Debug', data.debug_mode ? 'ON ⚠' : 'off'], ['Timezone', data.timezone || '—'])
    } else if (key === 'php-extensions') {
        Object.entries(data).filter(([k]) => !skip.has(k)).forEach(([ext, ok]) => rows.push([ext, ok ? '✓' : '✗']))
    } else if (key === 'akilimo-compute') {
        if (data.url) rows.push(['URL', data.url])
        if (data.http_status) rows.push(['HTTP', data.http_status])
        if (data.service_status) rows.push(['Service', data.service_status])
    }

    if (data.error) rows.push(['Error', data.error])
    return rows
}

function card(key: string, data: Check): string {
    const up = data.status === 'UP'
    const rows = detailRows(key, data)
        .map(([label, value]) => `
            <div class="d-flex justify-content-between gap-3 small py-1 border-top">
                <span class="text-body-secondary">${escapeHtml(label)}</span>
                <span class="text-end text-break">${escapeHtml(value)}</span>
            </div>`)
        .join('')

    return `
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 border-${up ? 'success' : 'danger'}-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-bold mb-0">${escapeHtml(SERVICE_LABELS[key] ?? key)}</h2>
                        <span class="badge text-bg-${up ? 'success' : 'danger'}">${up ? 'UP' : 'DOWN'}</span>
                    </div>
                    ${rows}
                </div>
            </div>
        </div>`
}

const el = (id: string) => document.getElementById(id) as HTMLElement
const statusBadge = el('health-status')
const statusLabel = el('health-label')
const statusTime = el('health-time')
const grid = el('health-grid')
const refresh = el('health-refresh') as HTMLButtonElement

const LOADING = `
    <div class="col-12 text-center text-body-secondary py-5">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Running health checks…
    </div>`

function setStatus(tone: 'secondary' | 'success' | 'danger', label: string) {
    statusBadge.className = `badge rounded-pill p-2 text-bg-${tone}`
    statusLabel.textContent = label
}

let loading = false

async function runCheck() {
    if (loading) return
    loading = true
    refresh.disabled = true
    grid.innerHTML = LOADING
    setStatus('secondary', 'Checking…')

    try {
        const data: HealthReport = await fetchHealth()
        const healthy = data.status === 'healthy'
        setStatus(healthy ? 'success' : 'danger', healthy ? 'All systems operational' : 'Degraded — some checks failed')
        statusTime.textContent = data.timestamp ? new Date(data.timestamp).toLocaleString() : '—'

        const checks = data.checks ?? {}
        const known = Object.keys(SERVICE_LABELS).filter((k) => k in checks)
        const rest = Object.keys(checks).filter((k) => !known.includes(k))
        grid.innerHTML = [...known, ...rest].map((k) => card(k, checks[k])).join('')
    } catch (err) {
        setStatus('danger', 'Failed to fetch health data')
        grid.innerHTML = `<div class="col-12"><div class="alert alert-danger" role="alert">Network error: ${escapeHtml((err as Error).message)}</div></div>`
    } finally {
        loading = false
        refresh.disabled = false
    }
}

refresh.addEventListener('click', runCheck)
runCheck()
