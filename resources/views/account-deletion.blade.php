<x-auth-layout
    title="Delete your AKILIMO account"
    heading="Delete your AKILIMO account"
    :subtitle="match ($step) {
        'request' => 'Step 1 of 2: confirm your email address',
        'confirm' => 'Step 2 of 2: enter the code we sent you',
        default => null,
    }"
>
    <x-slot:robots>noindex</x-slot:robots>

    @if (!empty($unavailable))
        <div class="alert alert-danger py-2 small" role="alert">
            Email is not available right now. Delete your account in the app, or write to akilimo@cgiar.org.
        </div>
    @endif
    @if (!empty($error))
        <div class="alert alert-danger py-2 small" role="alert">{{ $error }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 small" role="alert">{{ $errors->first() }}</div>
    @endif

    @if ($step === 'request')
        <p class="login-note">
            Enter the email address of your AKILIMO account. We send you a code to confirm it is yours.
            Your account and your saved advice are deleted for good. Advice you asked for before may be kept without your name, for statistics.
            You can also delete your account in the app: Settings, then Account.
        </p>

        <form method="POST" action="{{ route('account-deletion.request') }}">
            @csrf
            <div class="login-field">
                <label for="email" class="login-label">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    autofocus
                    placeholder="you@example.com"
                    value="{{ old('email') }}"
                    class="login-input{{ $errors->has('email') ? ' is-error' : '' }}"
                    required
                />
            </div>
            <button type="submit" class="login-btn">Send me a code</button>
        </form>
    @elseif ($step === 'confirm')
        <p class="login-note">
            If that address has an AKILIMO account, we sent it a code. Enter the code to delete the account. This cannot be undone.
        </p>

        <form method="POST" action="{{ route('account-deletion.confirm') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <div class="login-field">
                <label for="code" class="login-label">Code from the email</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    autocomplete="one-time-code"
                    autocapitalize="characters"
                    autofocus
                    class="login-input{{ $errors->has('code') ? ' is-error' : '' }}"
                    required
                />
            </div>
            <button type="submit" class="login-btn login-btn--danger">Delete my account</button>
        </form>

        <p class="login-switch">
            <a href="{{ route('account-deletion.show') }}">Use a different address or ask for a new code</a>
        </p>
    @else
        <div class="alert alert-success mb-0" role="alert">
            Your account has been deleted. Thank you for using AKILIMO.
        </div>
        <p class="login-switch"><a href="/">Back to home</a></p>
    @endif
</x-auth-layout>
