@props(['title', 'heading', 'subtitle' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @isset($robots)
        <meta name="robots" content="{{ $robots }}">
    @endisset
    <title>{{ $title }} — {{ config('app.name', 'AKILIMO API') }}</title>
    @include('partials.theme-head')
    @vite(['resources/scss/app.scss'])
</head>
<body>

{{-- The sign-in screen of the admin area, as Blade: same shell, same Bootstrap build --}}
<div class="login-page">
    <div class="login-slides" aria-hidden="true">
        <div class="login-slide login-slide--1"></div>
        <div class="login-slide login-slide--2"></div>
        <div class="login-slide login-slide--3"></div>
        <div class="login-slide login-slide--4"></div>
    </div>
    <div class="login-overlay"></div>

    <div class="login-card">
        <div class="login-logo-wrap">
            <a href="/" aria-label="AKILIMO home">
                <img src="/images/akilimo_logo_white.png" alt="Akilimo" class="login-logo">
            </a>
        </div>

        <div class="login-heading">
            <h1 class="login-title">{{ $heading }}</h1>
            @if ($subtitle)
                <p class="login-sub">{{ $subtitle }}</p>
            @endif
        </div>

        {{ $slot }}
    </div>
</div>

{{ $scripts ?? '' }}
</body>
</html>
