<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} - Polytechnic University of the Philippines</title>
    <meta name="theme-color" content="#8B0000">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/under-construction.css') }}?v={{ filemtime(public_path('assets/css/under-construction.css')) }}">
</head>
<body>
    <pup-header
        data-home="{{ route('public.home') }}"
        data-about="{{ route('public.about') }}"
        data-academics="{{ route('public.academics') }}"
        data-students="{{ route('public.students') }}"
        data-news-events="{{ route('public.events') }}"
        data-research="{{ route('public.research') }}"
        data-assets="{{ asset('assets') }}"
    ></pup-header>

    <main class="main-content construction-page">
        <section class="construction-hero">
            <div class="construction-hero-inner">
                <nav class="construction-breadcrumb reveal" aria-label="Breadcrumb">
                    <a href="{{ route('public.home') }}">Home</a>
                    <span>&gt;</span>
                    <strong>{{ $pageTitle }}</strong>
                </nav>

                <div class="construction-shell reveal">
                    <div class="construction-copy">
                        <p class="construction-kicker">{{ $eyebrow }}</p>
                        <h1>{{ $headline }}</h1>
                        <p class="construction-lead">
                            Coming Soon, page is under construction...
                        </p>
                        <p class="construction-body">{{ $description }}</p>

                        <div class="construction-actions">
                            @foreach ($actions as $action)
                                <a
                                    href="{{ $action['href'] }}"
                                    class="construction-button {{ $action['variant'] === 'secondary' ? 'is-secondary' : 'is-primary' }}"
                                >
                                    {{ $action['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <aside class="construction-status">
                        <div class="construction-status-badge">In Progress</div>
                        <h2>{{ $statusTitle }}</h2>
                        <p>{{ $statusText }}</p>

                        <div class="construction-note">
                            <span class="construction-note-dot" aria-hidden="true"></span>
                            <span>{{ $note }}</span>
                        </div>
                    </aside>
                </div>

                <section class="construction-highlights">
                    @foreach ($highlights as $highlight)
                        <article class="construction-card reveal">
                            <p class="construction-card-label">{{ $highlight['label'] }}</p>
                            <h3>{{ $highlight['title'] }}</h3>
                            <p>{{ $highlight['text'] }}</p>
                        </article>
                    @endforeach
                </section>
            </div>
        </section>
    </main>

    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
