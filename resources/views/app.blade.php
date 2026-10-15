<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'RECON • DICT Region 2') }}</title>
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <meta name="description" content="RECON — Regional Execution, Coordination & Operations Network for DICT Region 2">

        <!-- Google Fonts: Inter & JetBrains Mono -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Theme Anti-Flicker Script -->
        <script>
            (function() {
                try {
                    const savedTheme = localStorage.getItem('dict_tracker_theme');
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                        document.documentElement.setAttribute('data-theme', 'dark');
                    } else {
                        document.documentElement.setAttribute('data-theme', 'light');
                    }
                } catch (e) {}
            })();
        </script>

        <!-- Scripts & Styles -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
