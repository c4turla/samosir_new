<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'SAMOSIR') }}</title>
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Runtime configuration for Reverb -->
    <script>
        window.ReverbConfig = {
            key: '{{ env("VITE_REVERB_APP_KEY", env("REVERB_APP_KEY")) }}',
            host: '{{ env("VITE_REVERB_HOST", "localhost") }}',
            port: {{ env("VITE_REVERB_PORT", 8080) }},
            scheme: '{{ env("VITE_REVERB_SCHEME", "https") }}',
        };
        // Auto-fix 0.0.0.0 to actual hostname if misconfigured
        if (window.ReverbConfig.host === '0.0.0.0') {
            window.ReverbConfig.host = window.location.hostname;
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @routes
    @inertiaHead
</head>

<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    @inertia
</body>

</html>