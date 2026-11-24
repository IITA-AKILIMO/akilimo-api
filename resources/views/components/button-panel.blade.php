{{-- The three entry points of the welcome page; they sit on dark sections, hence the light variants --}}
<div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
    <x-button href="/docs/api" variant="light" class="btn-lg" :icon="view('components.icons.play')">
        API docs
    </x-button>

    @if (config('playground.enabled'))
        <x-button href="/playground" variant="outline-light" class="btn-lg" :icon="view('components.icons.play')">
            Sign in to try the API
        </x-button>

        <x-button href="/playground/health" variant="outline-light" class="btn-lg" :icon="view('components.icons.pulse')">
            Health status
        </x-button>
    @endif
</div>
