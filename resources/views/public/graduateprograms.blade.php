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
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">Apply Online</span>
                        <span class="iapply-schedule-box-value">
                            <a href="#">iApply Portal →</a>
                        </span>
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


        {{-- ── Contact Information Card ── --}}
        <div class="dp-contact-wrap">
            <div class="contact-info-card reveal">

                {{-- Brand / photo panel --}}
                <div class="dp-contact-photo-panel">
                    <div class="dp-contact-logo-wrap">
                        <img
                            src="{{ asset('assets/static_img/logo.png') }}"
                            alt="PUP Taguig Branch"
                            class="dp-contact-logo-img"
                            onerror="this.style.display='none'"
                        >
                    </div>
                    <div class="dp-contact-branch-info">
                        <p class="dp-contact-branch-name">PUP Taguig</p>
                        <p class="dp-contact-branch-sub">Branch Campus</p>
                    </div>
                    <div class="dp-contact-divider"></div>
                    <p class="dp-contact-address">
                        Gen. Santos Ave., Lower Bicutan, Taguig City, Metro Manila
                    </p>
                </div>

                {{-- Contact details panel --}}
                <div class="dp-contact-details-panel">

                    <div class="dp-contact-intro">
                        <span class="section-tag">Get in Touch</span>
                        <h2 class="dp-contact-heading">Want more information?</h2>
                        <p class="dp-contact-subtext">Contact PUP Taguig Branch today and our admissions team will be happy to assist you.</p>
                    </div>

                    <div class="dp-contact-rows">

                        {{-- Phone 1 --}}
                        <div class="dp-contact-row dp-contact-row--maroon">
                            <div class="dp-contact-icon dp-contact-icon--maroon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fffaf4" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.64 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.13 1 .37 1.98.71 2.93a2 2 0 0 1-.45 2.11L7.91 9a16 16 0 0 0 6 6l.96-.95a2 2 0 0 1 2.11-.45c.95.34 1.93.58 2.93.71A2 2 0 0 1 22 16.92z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="dp-contact-row-label">Telephone</span>
                                <a href="tel:+6328375858" class="dp-contact-row-value">(63 2) 837-5858</a>
                            </div>
                        </div>

                        {{-- Phone 2 --}}
                        <div class="dp-contact-row dp-contact-row--maroon">
                            <div class="dp-contact-icon dp-contact-icon--maroon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fffaf4" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.64 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.13 1 .37 1.98.71 2.93a2 2 0 0 1-.45 2.11L7.91 9a16 16 0 0 0 6 6l.96-.95a2 2 0 0 1 2.11-.45c.95.34 1.93.58 2.93.71A2 2 0 0 1 22 16.92z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="dp-contact-row-label">Telephone</span>
                                <a href="tel:+6328375859" class="dp-contact-row-value">(63 2) 837-5859</a>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="dp-contact-row dp-contact-row--gold">
                            <div class="dp-contact-icon dp-contact-icon--gold">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2d1606" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                </svg>
                            </div>
                            <div>
                                <span class="dp-contact-row-label">Email</span>
                                <a href="mailto:taguig@pup.edu.ph" class="dp-contact-row-value">taguig@pup.edu.ph</a>
                            </div>
                        </div>

                    </div>{{-- /.dp-contact-rows --}}

                    <a href="mailto:taguig@pup.edu.ph" class="apply-now-btn dp-contact-cta">
                        Send Us a Message
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>

                </div>{{-- /.dp-contact-details-panel --}}
            </div>{{-- /.contact-info-card --}}
        </div>{{-- /.dp-contact-wrap --}}

    </main>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>

</body>
</html>