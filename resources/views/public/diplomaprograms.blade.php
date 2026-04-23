<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diploma Programs - PUP Taguig Branch</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/academics.css') }}?v={{ filemtime(public_path('assets/css/academics.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
</head>
<body>
    @php
        $cmsPreview = (bool) ($cmsPreview ?? false);
        $academicsCms = \App\Support\AcademicsCmsContent::fromInput($academicsCms ?? [], null);
        $pageData = $academicsCms['pages']['diploma-programs'] ?? [];
    @endphp

    @unless($cmsPreview)
    <pup-header
        data-home="{{ route('public.home') }}"
        data-about="{{ route('public.about') }}"
        data-academics="{{ route('public.academics') }}"
        data-students="{{ route('public.students') }}"
        data-news-events="{{ route('public.events') }}"
        data-research="{{ route('public.research') }}"
        data-assets="{{ asset('assets') }}"
    ></pup-header>
    @endunless

    <main class="main-content">
<<<<<<< HEAD
        @include('partials.academics_program_page', [
            'programPageKey' => 'diploma-programs',
            'programPageTitle' => 'Diploma Programs',
            'pageData' => $pageData,
            'cmsPreview' => $cmsPreview,
        ])
=======

        {{-- ── Breadcrumb ── --}}
        <div class="academic-shell page-shell">
            <nav class="academic-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <a href="{{ route('public.academics') }}">Academics</a>
                <span>&gt;</span>
                <strong>Diploma Programs</strong>
            </nav>
        </div>

        {{-- ── Hero strip ── --}}
        <section class="iapply-hero">
            <div class="iapply-hero-content">
                <p class="iapply-hero-tag">PUP Taguig Branch</p>
                <h1>Diploma Programs</h1>
                <p class="iapply-hero-sub">Academic Year 2024–2025</p>
                <p>Gain practical, career-ready skills through our diploma programs designed for students who seek focused, industry-relevant training. PUP Taguig Branch offers CHED-recognized diploma courses that open pathways to employment and further study.</p>

                <div class="iapply-hero-desc">
                    <p>Offered Departments</p>
                    <ul>
                        <li>Department of Information and Communications Technology</li>
                        <li>Department of Office Administration</li>
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
                        <span class="iapply-schedule-box-value">CHED-Recognized Diploma Courses</span>
                    </div>
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">Mode of Study</span>
                        <span class="iapply-schedule-box-value">Face-to-face / Blended Learning</span>
                    </div>
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">Location</span>
                        <span class="iapply-schedule-box-value">PUP Taguig Branch, Gen. Santos Ave., Taguig City</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Diploma Programs Cards ── --}}
        <div class="contents-strip dp-programs-strip">
            <div class="contents-strip-inner">
                <div class="contents-strip-head reveal">
                    <span class="section-tag">Academic Offerings</span>
                    <h2>Diploma Programs</h2>
                </div>

        <div class="contents-cards dp-diploma-cards reveal delay-100">

            {{-- 01 DICT --}}
            <a href="#dict" class="contents-card" tabindex="0">
                <div class="dp-diploma-card-body">
                    <span class="dp-diploma-badge">DICT</span>
                    <h3 class="dp-diploma-title">Diploma in Information Communication Technology</h3>
                    <p class="dp-diploma-desc">A focused program covering computer systems, networking, and digital communications. Prepares graduates for technical roles in the ICT industry.</p>
                    <span class="dp-diploma-dept">Dept. of Information &amp; Communications Technology</span>
                </div>
            </a>

            {{-- 02 DOMT --}}
            <a href="#domt" class="contents-card" tabindex="0">
                <div class="dp-diploma-card-body">
                    <span class="dp-diploma-badge">DOMT</span>
                    <h3 class="dp-diploma-title">Diploma in Office Management Technology</h3>
                    <p class="dp-diploma-desc">Covers office procedures, records management, and business communications. Equips students for administrative and clerical careers in various industries.</p>
                    <span class="dp-diploma-dept">Dept. of Office Administration</span>
                </div>
            </a>

                </div>{{-- /.contents-cards --}}
            </div>{{-- /.contents-strip-inner --}}
        </div>{{-- /.contents-strip --}}

>>>>>>> eb45e5bc3866cfccc71eae761ec2fa3e5be8cedf
    </main>

    @unless($cmsPreview)
    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
    @else
    @include('partials.academics_preview_page_assets')
    @endunless
</body>
</html>
