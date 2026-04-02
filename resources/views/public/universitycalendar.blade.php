<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Calendar - PUP Taguig Branch</title>
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
                <strong>University Calendar</strong>
            </nav>
        </div>

        {{-- ── Hero strip ── --}}
        <section class="iapply-hero">
            <div class="iapply-hero-content">
                <p class="iapply-hero-tag">PUP Taguig Branch</p>
                <h1>University Calendar</h1>
                <p class="iapply-hero-sub">Academic Year 2024–2025</p>
                <p>Stay on top of your academic journey with PUP Taguig's official university calendar. Find important dates including enrollment periods, class schedules, holidays, examinations, and university-wide events.</p>

                <div class="iapply-hero-desc">
                    <p>Key Dates Include</p>
                    <ul>
                        <li>Enrollment & Registration periods</li>
                        <li>Start and end of classes</li>
                        <li>Midterm & Final examination schedules</li>
                        <li>Regular & special holidays</li>
                        <li>University events & activities</li>
                    </ul>
                </div>

                {{-- Floating photo panel --}}
                <div class="iapply-hero-visual dp-hero-photo-panel">
                    <img src="{{ asset('assets/static_img/campus_photo.jpg') }}" alt="PUP Taguig Campus" class="dp-hero-photo">
                </div>
            </div>
        </section>

        {{-- ── Quick Info strip ── --}}
        <div class="iapply-schedule-strip">
            <div class="iapply-schedule-inner">
                <div class="iapply-schedule-head">
                    <span class="section-tag">At a Glance</span>
                    <h2>Academic Year 2024–2025</h2>
                </div>
                <div class="iapply-schedule-grid">
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">1st Semester</span>
                        <span class="iapply-schedule-box-value">August – December 2024</span>
                    </div>
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">2nd Semester</span>
                        <span class="iapply-schedule-box-value">January – May 2025</span>
                    </div>
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">Summer</span>
                        <span class="iapply-schedule-box-value">June – July 2025</span>
                    </div>
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">Issued By</span>
                        <span class="iapply-schedule-box-value">Office of the University Registrar</span>
                    </div>
                    <div class="iapply-schedule-box">
                        <span class="iapply-schedule-box-label">Official Source</span>
                        <span class="iapply-schedule-box-value">
                            <a href="https://www.pup.edu.ph" target="_blank" rel="noopener">pup.edu.ph →</a>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Calendar Embed / Download Section ── --}}
        <div class="contents-strip dp-programs-strip">
            <div class="contents-strip-inner">

                <div class="contents-strip-head reveal">
                    <span class="section-tag">Official Calendar</span>
                    <h2>University Academic Calendar</h2>
                </div>

                {{-- Calendar PDF embed ── swap the src with the actual PDF/image path --}}
                <div class="uc-calendar-frame reveal delay-100">
                    <div class="uc-calendar-embed">
                        <iframe
                            src="{{ asset('assets/static_img/university_calendar.pdf') }}"
                            title="PUP University Academic Calendar AY 2024-2025"
                            class="uc-calendar-iframe"
                            loading="lazy"
                        ></iframe>
                    </div>
                    <div class="uc-calendar-actions reveal delay-200">
                        <a href="{{ asset('assets/static_img/university_calendar.pdf') }}" download class="apply-now-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Download Calendar
                        </a>
                        <a href="https://www.pup.edu.ph" target="_blank" rel="noopener" class="apply-now-btn uc-btn-outline">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            View on PUP Website
                        </a>
                    </div>
                </div>

            </div>
        </div>


    </main>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>

</body>
</html>