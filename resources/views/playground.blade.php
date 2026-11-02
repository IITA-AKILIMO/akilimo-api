<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Try the AKILIMO recommendations API — get fertilizer, intercropping, and planting schedule recommendations without an account.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Playground — {{ config('app.name', 'AKILIMO API') }}</title>

    @include('partials.theme-head')

    @viteReactRefresh
    @vite(['resources/scss/playground.scss', 'resources/js/playground/index.tsx', 'resources/js/theme-switcher.tsx'])
</head>
<body>

<nav class="pg-nav">
    <a href="/" class="pg-nav-brand" aria-label="AKILIMO home">
        <img src="/images/akilimo_logo_colored.png" alt="" class="pg-nav-brand-logo logo-on-light">
        <img src="/images/akilimo_logo_white.png" alt="" class="pg-nav-brand-logo logo-on-dark">
    </a>
    <span class="pg-nav-badge">Playground</span>
    <div class="pg-nav-user">
        <div data-theme-switcher></div>
        <a href="/playground/health" class="pg-nav-health-link" title="System status">
            <span class="pg-nav-health-dot" id="nav-health-dot"></span>
        </a>
        <span class="pg-nav-user-name">{{ auth()->user()->name }}</span>
        <form method="POST" action="/playground/logout">
            @csrf
            <button type="submit" class="pg-nav-signout">Sign out</button>
        </form>
    </div>
</nav>

<div id="playground-root"></div>

<script>
(function () {
    const dot = document.getElementById('nav-health-dot')
    if (!dot) return
    fetch('/health', { headers: { Accept: 'application/json' } })
        .then(r => r.json())
        .then(d => { dot.classList.add(d.status === 'healthy' ? 'is-up' : 'is-down') })
        .catch(() => { dot.classList.add('is-down') })
})()
</script>

</body>
</html>
