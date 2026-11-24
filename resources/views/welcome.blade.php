<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Agricultural intelligence API delivering fertilizer, intercropping, and planting schedule recommendations for smallholder farmers across Africa.">
    <title>{{ config('app.name', 'AKILIMO API') }}</title>
    @include('partials.theme-head')
    @viteReactRefresh
    @vite(['resources/scss/public.scss', 'resources/js/theme-switcher.tsx'])
</head>
<body>

<nav class="navbar bg-body border-bottom sticky-top">
    <div class="container-xl">
        <a href="/" class="navbar-brand me-auto" aria-label="AKILIMO home">
            <img src="/images/akilimo_logo_colored.png" alt="" height="36" class="logo-on-light">
            <img src="/images/akilimo_logo_white.png" alt="" height="36" class="logo-on-dark">
        </a>
        <div class="d-flex align-items-center gap-2">
            <div data-theme-switcher></div>
            <a href="/playground" class="btn btn-outline-secondary btn-sm">Playground</a>
        </div>
    </div>
</nav>

<header class="aki-hero text-center py-5">
    <div class="container-xl py-4">
        <p class="aki-eyebrow text-white-50 mb-3">Agricultural Intelligence API</p>
        <h1 class="display-4 fw-bold mb-3">
            Smart farming starts with<br><em>precise recommendations</em>
        </h1>
        <p class="lead text-white-50 mx-auto aki-lead">
            AKILIMO delivers data-driven fertilizer, intercropping, and planting
            schedule recommendations tailored to smallholder farmers across Africa.
        </p>
        <x-button-panel/>
    </div>
</header>

<div class="bg-body border-bottom">
    <div class="container-xl py-3">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
            @foreach ([
                ['Bearer + API Key', 'Authentication methods', 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'],
                ['Multi-country', 'Regional coverage', 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z'],
                ['Real-time Compute', 'With intelligent caching', 'M22 12h-4l-3 9L9 3l-3 9H2'],
                ['RESTful JSON', 'API standard', 'M16 18l6-6-6-6M8 6l-6 6 6 6'],
            ] as [$value, $label, $path])
                <div class="col d-flex align-items-center gap-3">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" class="text-primary flex-shrink-0" aria-hidden="true">
                        <path d="{{ $path }}"/>
                    </svg>
                    <div>
                        <div class="fw-semibold lh-sm">{{ $value }}</div>
                        <div class="small text-body-secondary">{{ $label }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container-xl">
        <p class="aki-eyebrow text-primary mb-2">Core Capabilities</p>
        <h2 class="fw-bold mb-3">Everything farmers need to grow smarter</h2>
        <p class="text-body-secondary mb-4 aki-lead">
            From soil nutrient analysis to seasonal planning, AKILIMO covers the full
            spectrum of agricultural intelligence for African smallholder farmers.
        </p>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
            @foreach ([
                ['🌱', 'Fertilizer Recommendations', 'Precise fertilizer types, quantities, and application timing based on crop type, soil data, and local market prices.'],
                ['🌿', 'Crop Intercropping', 'Optimized intercropping combinations for maize, cassava, and potatoes to maximise yield per hectare.'],
                ['📅', 'Planting Schedules', 'Data-driven planting calendars tailored to regional climate patterns and local market demand cycles.'],
                ['🌍', 'Multi-country Support', 'Country-specific fertilizer catalogs, currencies, and commodity price data across multiple African nations.'],
            ] as [$icon, $title, $text])
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="fs-3 mb-2" aria-hidden="true">{{ $icon }}</div>
                            <h3 class="h6 card-title">{{ $title }}</h3>
                            <p class="card-text small text-body-secondary mb-0">{{ $text }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-body-tertiary border-top border-bottom py-5">
    <div class="container-xl">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <p class="aki-eyebrow text-primary mb-2">Developer API</p>
                <h2 class="fw-bold mb-3">Simple integration, powerful results</h2>
                <p class="text-body-secondary">
                    A single POST endpoint delivers complete farm recommendations.
                    Authenticate with a short-lived bearer token or a long-lived API key.
                </p>

                <div class="d-flex gap-3 mt-4">
                    <div class="fs-4" aria-hidden="true">🔑</div>
                    <div>
                        <div class="fw-semibold">Bearer Token</div>
                        <div class="small text-body-secondary">
                            Obtain via <code>POST /v1/auth/login</code>, then pass as
                            <code>Authorization: Bearer &lt;token&gt;</code> on every request.
                            Tokens expire after a configurable TTL.
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-3 mt-3">
                    <div class="fs-4" aria-hidden="true">🗝️</div>
                    <div>
                        <div class="fw-semibold">API Key</div>
                        <div class="small text-body-secondary">
                            Long-lived key with scoped abilities. Pass as <code>X-Api-Key: ak_…</code>.
                            Generated and managed through the admin panel.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="aki-terminal" role="img" aria-label="Example request to the compute endpoint">
                    <div class="aki-terminal-bar" aria-hidden="true">
                        <span class="aki-terminal-dot aki-terminal-dot--red"></span>
                        <span class="aki-terminal-dot aki-terminal-dot--yellow"></span>
                        <span class="aki-terminal-dot aki-terminal-dot--green"></span>
                    </div>
<pre><span class="c-comment"># Compute farm recommendations</span>
<span class="c-method">POST</span> <span class="c-url">/v1/recommendations/compute</span>
<span class="c-key">Authorization:</span> <span class="c-str">Bearer &lt;token&gt;</span>
<span class="c-key">Content-Type:</span> <span class="c-str">application/json</span>

{
  <span class="c-key">"country"</span>: <span class="c-str">"NG"</span>,
  <span class="c-key">"use_case"</span>: <span class="c-str">"FR"</span>,
  <span class="c-key">"crop_type"</span>: <span class="c-str">"maize"</span>,
  <span class="c-key">"farm_size"</span>: <span class="c-num">2.5</span>,
  <span class="c-key">"currency"</span>: <span class="c-str">"NGN"</span>,
  <span class="c-key">"fertilizer_list"</span>: [
    {
      <span class="c-key">"key"</span>: <span class="c-str">"urea"</span>,
      <span class="c-key">"selected"</span>: <span class="c-bool">true</span>,
      <span class="c-key">"weight"</span>: <span class="c-num">50</span>,
      <span class="c-key">"price"</span>: <span class="c-num">12000</span>
    }
  ]
}

<span class="c-comment"># ← Returns optimised fertilizer rates,</span>
<span class="c-comment">#   intercrop options &amp; planting calendar</span></pre>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="aki-cta text-center py-5">
    <div class="container-xl py-3">
        <h2 class="fw-bold mb-3">Ready to get started?</h2>
        <p class="text-white-50 mx-auto aki-lead mb-0">
            Try the API instantly in the playground — no account needed.
            Or sign in to the admin panel to manage data and generate API keys.
        </p>
        <x-button-panel/>
    </div>
</section>

<footer class="border-top py-4 text-center small text-body-secondary">
    &copy; {{ date('Y') }} {{ config('app.name', 'AKILIMO API') }}
    &mdash; Agricultural recommendations for smallholder farmers
</footer>

</body>
</html>
