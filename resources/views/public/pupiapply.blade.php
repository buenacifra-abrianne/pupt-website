<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP iApply - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/academics.css') }}?v={{ filemtime(public_path('assets/css/academics.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pupiapply.css') }}?v={{ filemtime(public_path('assets/css/pupiapply.css')) }}">
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

        {{-- Breadcrumb --}}
        <div class="academic-shell page-shell">
            <nav class="academic-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <a href="{{ route('public.academics') }}">Academics</a>
                <span>&gt;</span>
                <strong>PUP iApply</strong>
            </nav>
        </div>

        {{-- Hero / Campus-Story Card --}}
        <section class="iapply-hero reveal">
            <p class="iapply-hero-tag">Admissions</p>
            <h1>PUP iApply</h1>
            <p class="iapply-hero-sub">CAEPUP — College Admission Evaluation of PUP</p>
            <p>
                PUP iApply (formerly PUPCET iApply), a Web-based Registration System, streamlines the
                University's ability to develop, deploy, and operate a massive admission process in a more
                efficient method, lower its costs of operation, and deliver a more efficient and reliable
                ICT-enabled system that effectively works for the community.
            </p>

            <div class="iapply-hero-desc">
                <p>System Benefits</p>
                <ul>
                    <li>Apply anytime at their convenience;</li>
                    <li>Save on cumulative expenses;</li>
                    <li>Save on time and energy; and</li>
                    <li>Verify status of application anytime.</li>
                </ul>
            </div>

            {{-- Floating panel --}}
            <div class="iapply-hero-visual">
                <div class="iapply-hero-visual-inner">
                    <div class="iapply-logo-icon">i</div>
                    <h3>Ready to Apply?</h3>
                    <p>
                        Enable applicants to register for University college admission evaluation
                        and entrance exams online.
                    </p>
                    <a href="#" class="apply-now-btn" target="_blank" rel="noopener">
                        Apply Now ↗
                    </a>
                </div>
            </div>
        </section>

        {{-- Schedule Strip --}}
        <div class="iapply-schedule-strip reveal delay-100">
            <div class="iapply-schedule-head">
                <span class="section-tag layout-kicker">Schedule &amp; Key Dates</span>
                <h2>Branch Campus — Taguig City</h2>
            </div>

            <div class="iapply-schedule-grid">
                <div class="iapply-schedule-box">
                    <span class="iapply-schedule-box-label">Online Application</span>
                    <span class="iapply-schedule-box-value">January 27, 2022 – May 30, 2022</span>
                </div>
                <div class="iapply-schedule-box">
                    <span class="iapply-schedule-box-label">Last Day of Issuance</span>
                    <span class="iapply-schedule-box-value">June 15, 2022</span>
                </div>
                <div class="iapply-schedule-box">
                    <span class="iapply-schedule-box-label">Evaluation Result</span>
                    <span class="iapply-schedule-box-value">June 15, 2022</span>
                </div>
                <div class="iapply-schedule-box">
                    <span class="iapply-schedule-box-label">Criteria</span>
                    <span class="iapply-schedule-box-value">
                        <a href="#" target="_blank" rel="noopener">View on Google Drive ↗</a>
                    </span>
                </div>
            </div>
        </div>

        {{-- Main Content Sections --}}
        <div class="iapply-sections-wrap">

            {{-- Step-by-step Guide --}}
            <div class="iapply-section-card reveal delay-100">
                <span class="section-tag layout-kicker">How to Apply</span>
                <h2>Step-by-step CAEPUP Application Guide</h2>
                <p>
                    Online application for the College Admission Evaluation of PUP <strong>#CAEPUP</strong>
                    for the First Semester, Academic Year 2022–2023.
                </p>

                {{-- YouTube Video Embed --}}
                <div class="iapply-video-wrap">
                    {{-- Replace the src below with the actual YouTube embed URL --}}
                    <iframe
                        src="https://youtu.be/A7Ed_9_nB50?si=voQVUuenHGtdWp8L"
                        title="CAEPUP Step-by-step Application Guide"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        loading="lazy"
                    ></iframe>
                </div>
            </div>

            {{-- Important Reminders --}}
            <div class="iapply-section-card reveal delay-200">
                <span class="section-tag layout-kicker">Before You Apply</span>
                <h2>Important — Please Read Carefully</h2>

                <div class="iapply-notice">
                    <p class="iapply-notice-title">Reminders</p>
                    <ul>
                        <li>Once your online application is finalized, no more editing of application.</li>
                        <li>Multiple accounts and multiple applications are grounds for disqualification.</li>
                        <li>Wrong entry of information and grades are grounds for disqualification.</li>
                    </ul>
                </div>

                <p>
                    <strong>Note:</strong> For general admission requirements, please read the
                    Specific Academic Program Criteria.
                </p>

                <p>
                    Before you apply online, please make sure that you have the following files
                    on your device or USB drive <em>(each file size must not be more than 300 kilobytes / KB)</em>:
                </p>

                <ol class="iapply-checklist">
                    <li>Applicant's photo (JPEG file — read photo guidelines)</li>
                    <li>
                        Grades in English, Math, Science and General Weighted Average (GWA) in
                        Grade 10; and Grades in all subjects in Grade 11 and GWA in Grade 11.
                    </li>
                    <li>Scanned Grade 10 Report Card (JPEG file)</li>
                    <li>Scanned Grade 11 Report Card (JPEG file)</li>
                    <li>
                        Report Cards must clearly show your complete name, LRN, grades in
                        English, Math, Science and GWA.
                    </li>
                </ol>
            </div>

        </div>{{-- end .iapply-sections-wrap --}}

    </main>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
