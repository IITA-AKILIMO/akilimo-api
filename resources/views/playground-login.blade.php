<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in — {{ config('app.name', 'AKILIMO API') }}</title>
    @include('partials.theme-head')
    @viteReactRefresh
    @vite(['resources/scss/playground.scss'])
</head>
<body>

<nav class="pg-nav pg-nav--glass">
    <a href="/" class="pg-nav-brand">
        <img src="/images/akilimo_logo_white.png" alt="Akilimo" class="pg-nav-brand-logo pg-nav-brand-logo--auth">
    </a>
    <span class="pg-nav-badge">Playground</span>
</nav>

<div class="pg-auth-wrap">
    <div class="pg-auth-slides" aria-hidden="true">
        <div class="pg-auth-slide pg-auth-slide--1"></div>
        <div class="pg-auth-slide pg-auth-slide--2"></div>
        <div class="pg-auth-slide pg-auth-slide--3"></div>
        <div class="pg-auth-slide pg-auth-slide--4"></div>
    </div>
    <div class="pg-auth-overlay"></div>
    <div class="pg-auth-card">
        <div class="pg-auth-heading">
            <h1 class="pg-auth-title">Welcome back</h1>
            <p class="pg-auth-sub">Sign in to access the playground</p>
        </div>

        @if ($errors->any())
            <div class="pg-auth-alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/playground/login" novalidate>
            @csrf

            <div class="pg-auth-field">
                <label for="login" class="pg-auth-label">Email or username</label>
                <input
                    id="login"
                    name="login"
                    type="text"
                    autocomplete="username"
                    autofocus
                    placeholder="you@example.com or john_doe"
                    value="{{ old('login') }}"
                    class="pg-auth-input{{ $errors->has('login') ? ' pg-auth-input--error' : '' }}"
                    required
                />
                @error('login')
                    <span class="pg-auth-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="pg-auth-field">
                <label for="password" class="pg-auth-label">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    class="pg-auth-input{{ $errors->has('password') ? ' pg-auth-input--error' : '' }}"
                    required
                />
                @error('password')
                    <span class="pg-auth-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="pg-auth-btn">Sign in</button>
        </form>

        <p class="pg-auth-switch">
            Don't have an account?
            <a href="/playground/register">Sign up</a>
        </p>
    </div>
</div>

</body>
</html>
