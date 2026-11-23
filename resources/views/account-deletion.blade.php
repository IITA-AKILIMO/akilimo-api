<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Delete your AKILIMO account</title>
    <style>
        :root { color-scheme: light dark; --g: #006f45; }
        body { font: 16px/1.5 system-ui, sans-serif; margin: 0; padding: 24px 16px; background: Canvas; color: CanvasText; }
        main { max-width: 480px; margin: 0 auto; }
        h1 { color: var(--g); font-size: 1.5rem; }
        label { display: block; font-weight: 600; margin: 16px 0 4px; }
        input { width: 100%; box-sizing: border-box; padding: 10px; font-size: 1rem; border: 1px solid #888; border-radius: 8px; }
        button { margin-top: 16px; padding: 12px 20px; font-size: 1rem; border: 0; border-radius: 8px; background: var(--g); color: #fff; cursor: pointer; }
        button.danger { background: #b3261e; }
        .error { color: #b3261e; margin-top: 12px; }
        .note { opacity: .75; font-size: .9rem; }
    </style>
</head>
<body>
<main>
    <h1>Delete your AKILIMO account</h1>

    @if ($step === 'request')
        <p>Enter the email address of your AKILIMO account. We send you a code to confirm it is yours.</p>
        <p class="note">Your account and your saved advice are deleted for good. Advice you asked for before may be kept without your name, for statistics. You can also delete your account in the app: Settings, then Account.</p>
        @if (!empty($unavailable))
            <p class="error">Email is not available right now. Delete your account in the app, or write to akilimo@cgiar.org.</p>
        @endif
        @if ($errors->any())
            <p class="error">{{ $errors->first() }}</p>
        @endif
        <form method="POST" action="{{ route('account-deletion.request') }}">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required autofocus>
            <button type="submit">Send me a code</button>
        </form>
    @elseif ($step === 'confirm')
        <p>If that address has an AKILIMO account, we sent it a code. Enter the code to delete the account. This cannot be undone.</p>
        @if (!empty($error))
            <p class="error">{{ $error }}</p>
        @endif
        @if ($errors->any())
            <p class="error">{{ $errors->first() }}</p>
        @endif
        <form method="POST" action="{{ route('account-deletion.confirm') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <label for="code">Code from the email</label>
            <input id="code" name="code" type="text" autocomplete="one-time-code" autocapitalize="characters" required autofocus>
            <button type="submit" class="danger">Delete my account</button>
        </form>
        <p class="note"><a href="{{ route('account-deletion.show') }}">Use a different address or ask for a new code</a></p>
    @else
        <p>Your account has been deleted. Thank you for using AKILIMO.</p>
    @endif
</main>
</body>
</html>
