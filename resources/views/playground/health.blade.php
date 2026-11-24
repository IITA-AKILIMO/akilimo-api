<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>System Health — {{ config('app.name', 'AKILIMO API') }}</title>
    @include('partials.theme-head')
    @vite(['resources/scss/playground.scss', 'resources/js/playground/health.ts', 'resources/js/theme-switcher.tsx'])
</head>
<body>

<x-playground.navbar badge="System Health" :back="true"/>

<main class="container-xl py-4">
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <span id="health-status" class="badge rounded-pill p-2 text-bg-secondary"><span class="visually-hidden">Status</span></span>
                <div>
                    <p id="health-label" class="fw-semibold mb-0" aria-live="polite">Checking…</p>
                    <p id="health-time" class="small text-body-secondary mb-0">—</p>
                </div>
            </div>
            <button id="health-refresh" type="button" class="btn btn-outline-secondary btn-sm">Refresh</button>
        </div>
    </div>

    <div id="health-grid" class="row g-3"></div>
</main>

</body>
</html>
