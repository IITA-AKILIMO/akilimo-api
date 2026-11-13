<div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">

    <x-button href="/docs/api"
              variant="terra"
              :icon="view('components.icons.play')">
        API Docs
    </x-button>

    <x-button href="/playground"
              variant="primary"
              :icon="view('components.icons.play')">
        Try the API
    </x-button>


{{--    <x-button href="/admin"--}}
{{--              variant="outline"--}}
{{--              :icon="view('components.icons.grid')">--}}
{{--        Admin Panel--}}
{{--    </x-button>--}}

    <x-button href="/playground/health"
              variant="outline"
              :icon="view('components.icons.pulse')">
        Health Status
    </x-button>
</div>
