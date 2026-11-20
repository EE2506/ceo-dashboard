<meta name="color-scheme" content="light dark">
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<script>
    if (window.theme) {
        document.documentElement.dataset.dashboardTheme = 'active';
    }

    try {
        // Seed Filament's persisted state once, before Alpine starts.
        if (localStorage.getItem('ceo-dashboard:sidebar-initialized') === null) {
            localStorage.setItem('filament:sidebar:desktop_open', 'false');
            localStorage.setItem('filament:sidebar:open', 'false');
            localStorage.setItem('ceo-dashboard:sidebar-initialized', 'true');
        }
    } catch {
        // Filament can still use its in-memory sidebar state when storage is blocked.
    }
</script>
