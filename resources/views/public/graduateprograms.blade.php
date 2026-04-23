<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graduate Programs - PUP Taguig Branch</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/academics.css') }}?v={{ filemtime(public_path('assets/css/academics.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
</head>
<body>

    <!-- Header -->
    <pup-header
        data-home="{{ route('public.home') }}"
        data-about="{{ route('public.about') }}"
        data-academics="{{ route('public.academics') }}"
        data-students="{{ route('public.students') }}"
        data-news-events="{{ route('public.events') }}"
        data-research="{{ route('public.research') }}"
        data-assets="{{ asset('assets') }}"
    ></pup-header>

    <main class="main-content">

        {{-- ── Breadcrumb ── --}}
        <div class="academic-shell page-shell">
            <nav class="academic-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <a href="{{ route('public.academics') }}">Academics</a>
                <span>&gt;</span>
                <strong>Graduate Programs</strong>
            </nav>
        </div>

        {{-- ── Hero strip ── --}}
        <section class="iapply-hero">
            <div class="iapply-hero-content">
                <p class="iapply-hero-tag">PUP Taguig Branch</p>
                <h1>Graduate Programs</h1>
                <p class="iapply-hero-sub">Open University System — Academic Year 2024–2025</p>
                <p>Advance your career through PUP's Open University System, offering flexible graduate education for working professionals. Our programs are designed to deepen expertise, foster research, and develop leaders committed to public service.</p>

                <div class="iapply-hero-desc">
                    <p>Delivery Mode</p>
                    <ul>
                        <li>Open University System (OUS)</li>
                        <li>Flexible / Blended Learning</li>
                        <li>Available to working professionals</li>
                    </ul>
                </div>

                {{-- Floating photo panel --}}
                <div class="iapply-hero-visual dp-hero-photo-panel">
                    <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP Taguig Campus" class="dp-hero-photo">
                </div>
            </div>
        </section>

        {{-- ── Quick Info strip ── --}}
        <div class="iapply-schedule-strip">
            <div class="iapply-schedule-inner">
                <div class="iapply-schedule-head">
                    <span class="section-tag">Quick Info</span>
                    <h2>Program Admission at a Glance</h2>
                </div>
                <div class="iapply-schedule-grid">
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">Admission</span>
                        <span class="iapply-schedule-box-value">Open for AY 2025–2026</span>
                    </div>
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">Program Type</span>
                        <span class="iapply-schedule-box-value">CHED-Recognized Graduate Program</span>
                    </div>
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">Delivery</span>
                        <span class="iapply-schedule-box-value">Open University System (OUS)</span>
                    </div>
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">Location</span>
                        <span class="iapply-schedule-box-value">PUP Taguig Branch, Gen. Santos Ave., Taguig City</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Graduate Programs Cards ── --}}
        <div class="contents-strip dp-programs-strip">
            <div class="contents-strip-inner">
                <div class="contents-strip-head reveal">
                    <span class="section-tag">Academic Offerings</span>
                    <h2>Graduate Programs</h2>
                </div>

                <div class="contents-cards dp-diploma-cards reveal delay-100">

                    {{-- 01 MEM --}}
                    <a href="#mem" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Master in Educational Management" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>Master in Educational Management</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>Master in Educational Management</h3>
                                    <p>MEM — Delivered via the Open University System. Develops educational leaders with expertise in curriculum, policy, and institutional management.</p>
                                </div>
                            </div>
                        </div>
                    </a>

                </div>{{-- /.contents-cards --}}
            </div>{{-- /.contents-strip-inner --}}
        </div>{{-- /.contents-strip --}}

    </main>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>

</body>
</html>