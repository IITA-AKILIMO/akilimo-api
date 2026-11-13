<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign up — {{ config('app.name', 'AKILIMO API') }}</title>
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
            <h1 class="pg-auth-title">Create an account</h1>
            <p class="pg-auth-sub">Get access to the API playground</p>
        </div>

        @if ($errors->any())
            <div class="pg-auth-alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/playground/register" novalidate id="register-form">
            @csrf

            <div class="pg-auth-field">
                <label for="name" class="pg-auth-label">Full name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    autocomplete="name"
                    autofocus
                    value="{{ old('name') }}"
                    class="pg-auth-input{{ $errors->has('name') ? ' pg-auth-input--error' : '' }}"
                    required
                />
                @error('name')
                    <span class="pg-auth-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="pg-auth-field">
                <label for="username" class="pg-auth-label">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    autocomplete="username"
                    value="{{ old('username') }}"
                    class="pg-auth-input{{ $errors->has('username') ? ' pg-auth-input--error' : '' }}"
                    placeholder="e.g. john_doe"
                    required
                />
                <span class="pg-auth-username-status" id="username-status" aria-live="polite"></span>
                <span class="pg-auth-hint">Lowercase letters, numbers and underscores only</span>
                @error('username')
                    <span class="pg-auth-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="pg-auth-field">
                <label for="email" class="pg-auth-label">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    value="{{ old('email') }}"
                    class="pg-auth-input{{ $errors->has('email') ? ' pg-auth-input--error' : '' }}"
                    required
                />
                @error('email')
                    <span class="pg-auth-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="pg-auth-field">
                <label for="password" class="pg-auth-label">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    class="pg-auth-input{{ $errors->has('password') ? ' pg-auth-input--error' : '' }}"
                    required
                />
                @error('password')
                    <span class="pg-auth-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="pg-auth-field">
                <label for="password_confirmation" class="pg-auth-label">Confirm password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="pg-auth-input{{ $errors->has('password_confirmation') ? ' pg-auth-input--error' : '' }}"
                    required
                />
                @error('password_confirmation')
                    <span class="pg-auth-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="pg-auth-btn" id="submit-btn">Create account</button>
        </form>

        <p class="pg-auth-switch">
            Already have an account?
            <a href="/playground/login">Sign in</a>
        </p>
    </div>
</div>

<script>
(function () {
    const input   = document.getElementById('username')
    const status  = document.getElementById('username-status')
    const submitBtn = document.getElementById('submit-btn')
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content

    let debounceTimer = null
    let lastChecked   = ''
    let isAvailable   = false

    function setState(state, message) {
        input.classList.remove('pg-auth-input--error', 'pg-auth-input--success')
        status.className = 'pg-auth-username-status'

        if (state === 'checking') {
            status.classList.add('pg-auth-username-status--checking')
            status.textContent = 'Checking…'
        } else if (state === 'available') {
            input.classList.add('pg-auth-input--success')
            status.classList.add('pg-auth-username-status--ok')
            status.textContent = '✓ ' + message
            isAvailable = true
        } else if (state === 'taken') {
            input.classList.add('pg-auth-input--error')
            status.classList.add('pg-auth-username-status--error')
            status.textContent = '✕ ' + message
            isAvailable = false
        } else if (state === 'invalid') {
            status.classList.add('pg-auth-username-status--hint')
            status.textContent = message
            isAvailable = false
        } else {
            status.textContent = ''
            isAvailable = false
        }
    }

    async function checkUsername(value) {
        if (value === lastChecked) return
        lastChecked = value

        if (value.length < 3) {
            setState(value.length > 0 ? 'invalid' : 'idle', 'At least 3 characters required')
            return
        }

        if (!/^[a-z0-9_]+$/.test(value)) {
            setState('invalid', 'Only lowercase letters, numbers and underscores')
            return
        }

        setState('checking')

        try {
            const res  = await fetch('/playground/check-username?username=' + encodeURIComponent(value), {
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            const data = await res.json()
            setState(data.available ? 'available' : 'taken', data.message)
        } catch {
            setState('idle')
        }
    }

    input.addEventListener('input', function () {
        const value = this.value.trim().toLowerCase()
        this.value  = value
        isAvailable = false

        clearTimeout(debounceTimer)

        if (value.length === 0) {
            setState('idle')
            return
        }

        debounceTimer = setTimeout(() => checkUsername(value), 380)
    })

    document.getElementById('register-form').addEventListener('submit', function (e) {
        const value = input.value.trim()
        if (value.length > 0 && !isAvailable) {
            e.preventDefault()
            setState('taken', 'Please choose an available username before submitting')
            input.focus()
        }
    })
})()
</script>

</body>
</html>
