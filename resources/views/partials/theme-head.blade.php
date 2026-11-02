{{-- Shared by the admin area and the API playground: fonts, colour theme, icons --}}
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#006f45">
<link rel="icon" href="/favicon.ico" sizes="16x16 32x32 48x48">
<link rel="icon" type="image/png" href="/images/favicon-192.png" sizes="192x192">
<link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet">
{{-- Apply the saved colour theme before first paint (kept in sync by components/ThemeSwitcher.tsx).
     `admin-theme` is the key the admin area used before the playground shared it. --}}
<script>
    (function () {
        var t;
        try { t = localStorage.getItem('akilimo-theme') || localStorage.getItem('admin-theme'); } catch (e) {}
        if (t !== 'light' && t !== 'dark') {
            t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-bs-theme', t);
    })();
</script>
