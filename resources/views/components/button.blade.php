@props([
    'href' => '#',
    'variant' => 'primary', // any Bootstrap button variant: primary, light, outline-light, outline-secondary, ...
    'icon' => null,
])

<a href="{{ $href }}" {{ $attributes->class(['btn', "btn-{$variant}"]) }}>
    @if($icon)
        {!! $icon !!}
    @endif
    {{ $slot }}
</a>
