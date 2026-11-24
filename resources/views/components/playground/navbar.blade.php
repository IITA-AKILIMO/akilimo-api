{{-- Top bar of the playground pages: Bootstrap navbar, logo (swapped for dark mode), badge, theme switch.
     badge: the label next to the logo; health: show the status dot; back: link back to the playground --}}
@props(['badge' => 'Playground', 'health' => false, 'back' => false])
<nav class="navbar bg-body border-bottom sticky-top">
    <div class="container-xl">
        <a href="{{ $back ? '/playground' : '/' }}" class="navbar-brand me-2" aria-label="AKILIMO home">
            <img src="/images/akilimo_logo_colored.png" alt="" height="36" class="logo-on-light">
            <img src="/images/akilimo_logo_white.png" alt="" height="36" class="logo-on-dark">
        </a>
        <span class="badge rounded-pill text-bg-primary text-uppercase me-auto d-none d-sm-inline">{{ $badge }}</span>
        <div class="d-flex align-items-center gap-2">
            <div data-theme-switcher></div>
            @if ($health)
                <a href="/playground/health" class="text-decoration-none" title="System status">
                    <span class="badge rounded-pill text-bg-secondary p-2" data-health-dot><span class="visually-hidden">System status</span></span>
                </a>
            @endif
            @auth
                <span class="small text-body-secondary d-none d-md-inline">{{ auth()->user()->name }}</span>
                @if ($back)
                    <a href="/playground" class="btn btn-outline-secondary btn-sm">← Playground</a>
                @else
                    <form method="POST" action="/playground/logout" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Sign out</button>
                    </form>
                @endif
            @else
                @if ($back)
                    <a href="/playground" class="btn btn-outline-secondary btn-sm">← Playground</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
