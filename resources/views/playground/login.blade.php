<x-auth-layout title="Sign in" heading="Welcome back" subtitle="Sign in to access the playground">
    @if ($errors->any())
        <div class="alert alert-danger py-2 small" role="alert">{{ $errors->first() }}</div>
    @endif

        <form method="POST" action="/playground/login" novalidate>
            @csrf

            <div class="login-field">
                <label for="login" class="login-label">Email or username</label>
                <input
                    id="login"
                    name="login"
                    type="text"
                    autocomplete="username"
                    autofocus
                    placeholder="you@example.com or john_doe"
                    value="{{ old('login') }}"
                    class="login-input{{ $errors->has('login') ? ' is-error' : '' }}"
                    required
                />
                @error('login')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="login-field">
                <label for="password" class="login-label">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    class="login-input{{ $errors->has('password') ? ' is-error' : '' }}"
                    required
                />
                @error('password')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="login-btn">Sign in</button>
        </form>

        <p class="login-switch">
            Don't have an account?
            <a href="/playground/register">Sign up</a>
        </p>
</x-auth-layout>
