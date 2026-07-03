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
</head>
<body>

<main class="menu">
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
                <a href="{{ rtrim(config('services.idp.base_url'), '/') . '/api/v1/auth/authorize?client_id=' . urlencode(config('services.idp.client_id')) }}" class="portal-button portal-button--primary">
                    <span class="icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </span>
                    <span class="text">
                        <strong>Login</strong>
                        <small>Faculty and staff sign in</small>
                    </span>
                </a>

                <a href="{{ rtrim(config('services.oneportal.url'), '/') }}" class="portal-button portal-button--primary">
                    <span class="icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </span>
                    <span class="text">
                        <strong>One Portal</strong>
                        <small>Access all systems within the PUP-Taguig Campus</small>
                    </span>
                </a>

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
