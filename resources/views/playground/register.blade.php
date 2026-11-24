<x-auth-layout title="Sign up" heading="Create an account" subtitle="Get access to the API playground">
    @if ($errors->any())
        <div class="alert alert-danger py-2 small" role="alert">{{ $errors->first() }}</div>
    @endif

        <form method="POST" action="/playground/register" novalidate id="register-form">
            @csrf

            <div class="login-field">
                <label for="name" class="login-label">Full name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    autocomplete="name"
                    autofocus
                    value="{{ old('name') }}"
                    class="login-input{{ $errors->has('name') ? ' is-error' : '' }}"
                    required
                />
                @error('name')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="login-field">
                <label for="username" class="login-label">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    autocomplete="username"
                    value="{{ old('username') }}"
                    class="login-input{{ $errors->has('username') ? ' is-error' : '' }}"
                    placeholder="e.g. john_doe"
                    required
                />
                <span class="login-status" id="username-status" aria-live="polite"></span>
                <span class="login-hint">Lowercase letters, numbers and underscores only</span>
                @error('username')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="login-field">
                <label for="email" class="login-label">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    value="{{ old('email') }}"
                    class="login-input{{ $errors->has('email') ? ' is-error' : '' }}"
                    required
                />
                @error('email')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="login-field">
                <label for="password" class="login-label">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    class="login-input{{ $errors->has('password') ? ' is-error' : '' }}"
                    required
                />
                @error('password')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="login-field">
                <label for="password_confirmation" class="login-label">Confirm password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="login-input{{ $errors->has('password_confirmation') ? ' is-error' : '' }}"
                    required
                />
                @error('password_confirmation')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="login-btn" id="submit-btn">Create account</button>
        </form>

        <p class="login-switch">
            Already have an account?
            <a href="/playground/login">Sign in</a>
        </p>

    <x-slot:scripts>
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
        input.classList.remove('is-error', 'is-success')
        status.className = 'login-status'

        if (state === 'checking') {
            status.classList.add('login-status--checking')
            status.textContent = 'Checking…'
        } else if (state === 'available') {
            input.classList.add('is-success')
            status.classList.add('login-status--ok')
            status.textContent = '✓ ' + message
            isAvailable = true
        } else if (state === 'taken') {
            input.classList.add('is-error')
            status.classList.add('login-status--error')
            status.textContent = '✕ ' + message
            isAvailable = false
        } else if (state === 'invalid') {
            status.classList.add('login-status--hint')
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
    </x-slot:scripts>
</x-auth-layout>
