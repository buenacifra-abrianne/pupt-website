<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $statusCode }} {{ $headline }} - PUP Taguig Campus</title>
    <meta name="theme-color" content="#8B0000">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="{{ asset('assets/css/errors.css') }}?v={{ filemtime(public_path('assets/css/errors.css')) }}">
</head>
<body class="error-page">
    <main class="error-shell">
        <section class="error-panel" aria-labelledby="error-title">
            <div class="error-brand">
                <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP Taguig Campus logo" class="error-brand-logo">
                <div class="error-brand-copy">
                    <p class="error-kicker">PUP Taguig Campus</p>
                    <p class="error-subkicker">Official Campus Website</p>
                </div>
            </div>

            <div class="error-content">
                <p class="error-code" aria-label="Error code {{ $statusCode }}">{{ $statusCode }}</p>
                <h1 id="error-title">{{ $headline }}</h1>
                <p class="error-message">{{ $message }}</p>

                <a href="{{ route('public.landing') }}" class="error-home-link">Go Back to Homepage</a>
            </div>
        </section>
    </main>
</body>
</html>
