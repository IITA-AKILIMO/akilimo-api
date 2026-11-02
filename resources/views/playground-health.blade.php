<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>System Health — {{ config('app.name', 'AKILIMO API') }}</title>
    @include('partials.theme-head')
    @vite(['resources/scss/playground.scss'])
</head>
<body class="pg-health-body">

<nav class="pg-nav pg-nav--glass">
    <a href="/playground" class="pg-nav-brand">
        <img src="/images/akilimo_logo_white.png" alt="Akilimo" class="pg-nav-brand-logo pg-nav-brand-logo--auth">
    </a>
    <span class="pg-nav-badge">System Health</span>
    <div class="pg-nav-user">
        <span class="pg-nav-user-name" style="color:rgba(255,255,255,0.6)">{{ auth()->user()->name }}</span>
        <a href="/playground" class="pg-nav-signout" style="color:rgba(255,255,255,0.5);border-color:rgba(255,255,255,0.15)">← Playground</a>
    </div>
</nav>

<div class="pg-health-bg">
    <div class="pg-auth-slides" aria-hidden="true">
        <div class="pg-auth-slide pg-auth-slide--1"></div>
        <div class="pg-auth-slide pg-auth-slide--2"></div>
        <div class="pg-auth-slide pg-auth-slide--3"></div>
        <div class="pg-auth-slide pg-auth-slide--4"></div>
    </div>
    <div class="pg-auth-overlay"></div>
</div>

<div class="pg-health-wrap">

    {{-- Hero status bar --}}
    <div class="ph-hero" id="ph-hero">
        <div class="ph-hero-inner">
            <div class="ph-status-ring" id="ph-ring">
                <span class="ph-status-pulse" id="ph-pulse"></span>
            </div>
            <div>
                <p class="ph-status-label" id="ph-status-label">Checking…</p>
                <p class="ph-status-ts" id="ph-status-ts">—</p>
            </div>
        </div>
        <button class="ph-refresh-btn" id="ph-refresh" type="button">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" id="ph-refresh-icon">
                <path d="M21 2v6h-6M3 12a9 9 0 0 1 15-6.7L21 8M3 22v-6h6M21 12a9 9 0 0 1-15 6.7L3 16"/>
            </svg>
            Refresh
        </button>
    </div>

    {{-- Service cards grid --}}
    <div class="ph-grid" id="ph-grid">
        <div class="ph-loading">
            <div class="ph-spinner"></div>
            <span>Running health checks…</span>
        </div>
    </div>

</div>

<script>
(function () {
    const SERVICE_META = {
        'database':        { label: 'Database',         icon: '<path d="M12 2C7.58 2 4 3.79 4 6v12c0 2.21 3.58 4 8 4s8-1.79 8-4V6c0-2.21-3.58-4-8-4z"/><path d="M4 6c0 2.21 3.58 4 8 4s8-1.79 8-4"/><path d="M4 12c0 2.21 3.58 4 8 4s8-1.79 8-4"/>' },
        'redis':           { label: 'Redis',             icon: '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4.03 3-9 3S3 13.66 3 12"/><path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/>' },
        'cache':           { label: 'Cache',             icon: '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>' },
        'storage':         { label: 'File Storage',      icon: '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>' },
        'queue':           { label: 'Queue',             icon: '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>' },
        'mail':            { label: 'Mail',              icon: '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>' },
        'disk-space':      { label: 'Disk Space',        icon: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>' },
        'migrations':      { label: 'Migrations',        icon: '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>' },
        'env-config':      { label: 'Environment',       icon: '<circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>' },
        'php-extensions':  { label: 'PHP Extensions',    icon: '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>' },
        'akilimo-compute': { label: 'Akilimo Compute',   icon: '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>' },
    }

    const formatBytes = (bytes) => {
        if (!bytes || isNaN(bytes)) return '—'
        const gb = bytes / 1073741824
        if (gb >= 1) return gb.toFixed(1) + ' GB'
        return (bytes / 1048576).toFixed(0) + ' MB'
    }

    const renderDetails = (key, data) => {
        const rows = []
        const skip = new Set(['status', 'error'])

        if (key === 'database') {
            rows.push(['Database', data.database || '—'])
            rows.push(['Driver', data.database_type || '—'])
            rows.push(['Tables', data.total_tables ?? '—'])
        } else if (key === 'redis') {
            rows.push(['Version', data.version || '—'])
            rows.push(['Used mem', data.memory?.used || '—'])
            rows.push(['Peak mem', data.memory?.peak || '—'])
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
                const cls = t.class || t.constructor?.name || 'unknown'
                rows.push(['Transport', cls.split('\\').pop()])
            } else {
                rows.push(['Transport', String(t || '—')])
            }
        } else if (key === 'disk-space') {
            rows.push(['Used', data.used_percentage || '—'])
            rows.push(['Free', formatBytes(data.free_space)])
            rows.push(['Total', formatBytes(data.total_space)])
        } else if (key === 'migrations') {
            rows.push(['Applied', data.total_migrations ?? '—'])
        } else if (key === 'env-config') {
            rows.push(['Debug', data.debug_mode ? 'ON ⚠' : 'off'])
            rows.push(['Timezone', data.timezone || '—'])
        } else if (key === 'php-extensions') {
            const exts = Object.entries(data).filter(([k]) => !skip.has(k))
            exts.forEach(([ext, ok]) => rows.push([ext, ok ? '✓' : '✗']))
        } else if (key === 'akilimo-compute') {
            if (data.url) rows.push(['URL', data.url])
            if (data.http_status) rows.push(['HTTP', data.http_status])
            if (data.service_status) rows.push(['Service', data.service_status])
        }

        if (data.error) rows.push(['Error', data.error])

        return rows.map(([label, val]) =>
            `<div class="ph-detail-row">
                <span class="ph-detail-label">${label}</span>
                <span class="ph-detail-value">${val}</span>
            </div>`
        ).join('')
    }

    const buildCard = (key, data) => {
        const meta   = SERVICE_META[key] || { label: key, icon: '<circle cx="12" cy="12" r="10"/>' }
        const isUp   = data.status === 'UP'
        const statusClass = isUp ? 'ph-card--up' : 'ph-card--down'
        const badgeClass  = isUp ? 'ph-badge--up' : 'ph-badge--down'
        const badgeTxt    = isUp ? 'UP' : 'DOWN'

        return `
        <div class="ph-card ${statusClass}">
            <div class="ph-card-head">
                <div class="ph-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        ${meta.icon}
                    </svg>
                </div>
                <span class="ph-card-name">${meta.label}</span>
                <span class="ph-badge ${badgeClass}">${badgeTxt}</span>
            </div>
            <div class="ph-card-details">
                ${renderDetails(key, data)}
            </div>
        </div>`
    }

    const hero = document.getElementById('ph-hero')
    const ring = document.getElementById('ph-ring')
    const pulse = document.getElementById('ph-pulse')
    const label = document.getElementById('ph-status-label')
    const ts    = document.getElementById('ph-status-ts')
    const grid  = document.getElementById('ph-grid')
    const btn   = document.getElementById('ph-refresh')
    const icon  = document.getElementById('ph-refresh-icon')

    let loading = false

    function setLoading(v) {
        loading = v
        btn.disabled = v
        icon.style.animation = v ? 'spin 0.7s linear infinite' : ''
    }

    async function runCheck() {
        if (loading) return
        setLoading(true)

        grid.innerHTML = '<div class="ph-loading"><div class="ph-spinner"></div><span>Running health checks…</span></div>'
        label.textContent = 'Checking…'
        ring.className = 'ph-status-ring ph-status-ring--checking'
        pulse.className = 'ph-status-pulse'

        try {
            const res  = await fetch('/health', { headers: { Accept: 'application/json' } })
            const data = await res.json()

            const healthy = data.status === 'healthy'
            label.textContent = healthy ? 'All systems operational' : 'Degraded — some checks failed'
            ts.textContent    = data.timestamp ? new Date(data.timestamp).toLocaleString() : '—'
            ring.className    = 'ph-status-ring ' + (healthy ? 'ph-status-ring--up' : 'ph-status-ring--down')
            pulse.className   = 'ph-status-pulse ' + (healthy ? 'ph-status-pulse--up' : 'ph-status-pulse--down')

            const checks = data.checks || {}
            const order  = Object.keys(SERVICE_META).filter(k => k in checks)
            const rest   = Object.keys(checks).filter(k => !order.includes(k))
            const sorted = [...order, ...rest]

            grid.innerHTML = sorted.map(k => buildCard(k, checks[k])).join('')

        } catch (err) {
            label.textContent = 'Failed to fetch health data'
            ring.className    = 'ph-status-ring ph-status-ring--down'
            pulse.className   = 'ph-status-pulse ph-status-pulse--down'
            grid.innerHTML    = `<div class="ph-loading ph-loading--error">Network error: ${err.message}</div>`
        } finally {
            setLoading(false)
        }
    }

    btn.addEventListener('click', runCheck)
    runCheck()
})()
</script>

</body>
</html>
