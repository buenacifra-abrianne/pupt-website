<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polytechnic University of the Philippines - Taguig Campus</title>
    <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}?v={{ filemtime(public_path('assets/css/index.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/landing-footer.css') }}?v={{ filemtime(public_path('assets/css/components/landing-footer.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <meta name="theme-color" content="#8B0000">
    <style>
        :root {
            --landing-bg-image: url("{{ asset('assets/static_img/bg_landing_page.jpg') }}");
        }
    </style>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="Polytechnic University of the Philippines - Taguig Campus" />
    <meta property="og:description" content="Welcome to the PUP Taguig Campus Website" />
    <meta property="og:image" content="{{ asset('assets/static_img/logo.png') }}" />

</head>
<body>
<script>
    (function() {
        try {
            var isDark = localStorage.getItem('pup-dark-mode') === 'true';
            if (isDark) {
                document.body.classList.add('pup-dark-mode');
            }
        } catch (e) {}
    })();
</script>
<main class="menu">
    <button class="theme-switch" aria-label="Toggle dark mode" onclick="toggleDarkMode()">
        <div class="theme-switch-track">
            <svg class="theme-switch-icon theme-switch-icon--sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
            <svg class="theme-switch-icon theme-switch-icon--moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
            <div class="theme-switch-thumb"></div>
        </div>
    </button>
    <script>
        function toggleDarkMode() {
            var isDark = document.body.classList.toggle('pup-dark-mode');
            localStorage.setItem('pup-dark-mode', isDark ? 'true' : 'false');
        }
    </script>
    <div class="landing-container">
        <section class="landing-identity">
            <h1 class="title">
                <span class="title-welcome">WELCOME TO</span>
                <span class="title-main-line">PUP TAGUIG</span>
                <span class="title-main-line">WEBSITE</span>
            </h1>

            <p class="kicker">Official Campus Website</p>

            <div class="landing-seals-shell" aria-label="Government and campus seals">
                <div class="support-logo-row">
                    <span class="support-logo-item">
                        <img
                            class="support-logo"
                            src="{{ asset('assets/static_img/ARTA.png') }}"
                            alt="ARTA Seal"
                        >
                    </span>
                    <span class="support-logo-item">
                        <img
                            class="support-logo"
                            src="{{ asset('assets/static_img/TUVSUD%20ISO.png') }}"
                            alt="TUVSU Seal"
                        >
                    </span>
                    <span class="support-logo-item">
                        <img
                            class="support-logo"
                            src="{{ asset('assets/static_img/transparency_seal.png') }}"
                            alt="Transparency Seal"
                        >
                    </span>
                    <span class="support-logo-item">
                        <img
                            class="support-logo"
                            src="{{ asset('assets/static_img/freedom_of_information.png') }}"
                            alt="Freedom of Information"
                        >
                    </span>
                    <span class="support-logo-item support-logo-item--dpo">
                        <img
                            class="support-logo support-logo--dpo"
                            src="{{ asset('assets/static_img/DPO_DPS_seal.png') }}"
                            alt="DPO DPS Seal"
                        >
                    </span>
                </div>
            </div>

        </section>

        <section class="landing-actions" aria-label="Landing actions">
            <div class="landing-actions-seals-row" aria-label="Primary seals">
                <img
                    class="landing-actions-seal landing-actions-seal--pup"
                    src="{{ asset('assets/static_img/logo.png') }}"
                    alt="PUP Seal"
                >
                <img
                    class="landing-actions-seal landing-actions-seal--bagong"
                    src="{{ asset('assets/static_img/bagong_pilipinas_logo.png') }}"
                    alt="Bagong Pilipinas Logo"
                >
            </div>

            <h2>Choose Destination</h2>
            <p>Access faculty services or enter the public homepage.</p>

            @if (session('no_role_error'))
                <div class="landing-alert landing-alert-error">
                    {{ session('no_role_error') }}
                </div>
            @endif

            @if (session('error'))
                <div class="landing-alert landing-alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <div class="button-container">
                @if ($isIdpOnline ?? true)
                    <a href="{{ rtrim(config('services.idp.base_url'), '/') . '/api/v1/auth/authorize?client_id=' . urlencode(config('services.idp.client_id')) }}" class="portal-button portal-button--primary">
                        <span class="icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                            </svg>
                        </span>
                        <span class="text">
                            <strong>Login</strong>
                            <small>Faculty and staff sign in</small>
                        </span>
                    </a>
                @else
                    <a href="{{ route('superadmin.login') }}" class="portal-button portal-button--primary">
                        <span class="icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </span>
                        <span class="text">
                            <strong>Emergency Login</strong>
                            <small>The unified login system is currently unreachable. Use our emergency login.</small>
                        </span>
                    </a>
                @endif

                @if ($isIdpOnline ?? true)
                    <a href="{{ rtrim(config('services.oneportal.url'), '/') }}" class="portal-button portal-button--primary">
                        <span class="icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                            </svg>
                        </span>
                        <span class="text">
                            <strong>One Portal</strong>
                            <small>Access all systems within the PUP-Taguig Campus</small>
                        </span>
                    </a>
                @endif

                <a href="{{ route('public.home.callback') }}" class="portal-button portal-button--homepage">
                    <span class="icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </span>
                    <span class="text">
                        <strong>View Homepage</strong>
                        <small>Browse news, events, and public pages</small>
                    </span>
                </a>
            </div>

        </section>
    </div>

    <x-landing-footer />
</main>

</body>
</html>
