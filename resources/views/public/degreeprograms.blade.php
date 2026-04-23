<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Degree Programs - PUP Taguig Branch</title>
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
                <strong>Degree Programs</strong>
            </nav>
        </div>

        {{-- ── Hero strip ── --}}
        <section class="iapply-hero">
            <div class="iapply-hero-content">
                <p class="iapply-hero-tag">PUP Taguig Branch</p>
                <h1>Degree Programs</h1>
                <p class="iapply-hero-sub">Academic Year 2024–2025</p>
                <p>Pursue excellence through our CHED-accredited degree programs designed to develop globally competitive graduates. PUP Taguig Branch offers quality education rooted in science, technology, and professional practice.</p>

                <div class="iapply-hero-desc">
                    <p>Offered Colleges</p>
                    <ul>
                        <li>College of Engineering</li>
                        <li>College of Accountancy and Finance</li>
                        <li>College of Business Administration</li>
                        <li>College of Science</li>
                        <li>College of Education</li>
                        <li>College of Office Administration and Business Education</li>
                        <li>College of Social Sciences and Development</li>
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
                        <span class="iapply-schedule-box-value">CHED-Accredited BS &amp; AB Degrees</span>
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

        {{-- ── Degree Programs Cards ── --}}
        <div class="contents-strip dp-programs-strip">
            <div class="contents-strip-inner">
                <div class="contents-strip-head reveal">
                    <span class="section-tag">Academic Offerings</span>
                    <h2>Bachelor's Degree Programs</h2>
                </div>

                <div class="contents-cards reveal delay-100">

                    {{-- 01 BSECE --}}
                    <a href="#bsece" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Electronics Engineering" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>BS Electronics Engineering</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>BS Electronics Engineering</h3>
                                    <p>BSECE — A rigorous program covering circuits, communications, and embedded systems. Graduates are equipped to sit for the ECE Licensure Examination.</p>
                                </div>
                                <span class="contents-card-action">View Program</span>
                            </div>
                        </div>
                    </a>

                    {{-- 02 BSME --}}
                    <a href="#bsme" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Mechanical Engineering" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>BS Mechanical Engineering</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>BS Mechanical Engineering</h3>
                                    <p>BSME — Covers thermodynamics, fluid mechanics, and machine design. Prepares students for the Mechanical Engineering Licensure Exam.</p>
                                </div>
                                <span class="contents-card-action">View Program</span>
                            </div>
                        </div>
                    </a>

                    {{-- 03 BSA --}}
                    <a href="#bsa" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Accountancy" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>BS Accountancy</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>BS Accountancy</h3>
                                    <p>BSA — A comprehensive program in financial reporting, auditing, and taxation. Aligned with the CPA Licensure Examination competencies.</p>
                                </div>
                                <span class="contents-card-action">View Program</span>
                            </div>
                        </div>
                    </a>

                    {{-- 04 BSBA --}}
                    <a href="#bsba" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Business Administration" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>BS Business Administration</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>BS Business Administration</h3>
                                    <p>BSBA — Offered with majors in <strong class="dp-card-major">Human Resource Development Management</strong> and <strong class="dp-card-major">Marketing Management</strong>.</p>
                                </div>
                                <span class="contents-card-action">View Program</span>
                            </div>
                        </div>
                    </a>

                    {{-- 05 BSAM --}}
                    <a href="#bsam" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Applied Mathematics" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>BS Applied Mathematics</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>BS Applied Mathematics</h3>
                                    <p>BSAM — Develops strong analytical and problem-solving skills for careers in data science, finance, and research.</p>
                                </div>
                                <span class="contents-card-action">View Program</span>
                            </div>
                        </div>
                    </a>

                    {{-- 06 BSIT --}}
                    <a href="#bsit" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Information Technology" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>BS Information Technology</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>BS Information Technology</h3>
                                    <p>BSIT — Covers software development, networking, and database systems. Prepares students for the IT Licensure Examination.</p>
                                </div>
                                <span class="contents-card-action">View Program</span>
                            </div>
                        </div>
                    </a>

                    {{-- 07 BSEntrep --}}
                    <a href="#bsentrep" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Entrepreneurship" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>BS Entrepreneurship</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>BS Entrepreneurship</h3>
                                    <p>BSENTREP — Equips students with the mindset and skills to launch and manage successful ventures in the Philippine and global market.</p>
                                </div>
                                <span class="contents-card-action">View Program</span>
                            </div>
                        </div>
                    </a>

                    {{-- 08 BSED --}}
                    <a href="#bsed" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Secondary Education" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>Bachelor in Secondary Education</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>Bachelor in Secondary Education</h3>
                                    <p>BSED — Offered with majors in <strong class="dp-card-major">English</strong> and <strong class="dp-card-major">Mathematics</strong>. Aligned with the LET competency standards.</p>
                                </div>
                                <span class="contents-card-action">View Program</span>
                            </div>
                        </div>
                    </a>

                    {{-- 09 BSOA --}}
                    <a href="#bsoa" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Office Administration" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>BS Office Administration</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>BS Office Administration</h3>
                                    <p>BSOA — Trains students in records management, office systems, and administrative operations for both public and private sectors.</p>
                                </div>
                                <span class="contents-card-action">View Program</span>
                            </div>
                        </div>
                    </a>

                    {{-- 10 BSPsy --}}
                    <a href="#bspsy" class="contents-card card_without_section" tabindex="0">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Psychology" loading="lazy">
                                <div class="contents-card-copy">
                                    <h3>BS Psychology</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>BS Psychology</h3>
                                    <p>BSPSY — Studies human behavior, mental processes, and psychological assessment. Prepares students for careers in counseling, HR, and research.</p>
                                </div>
                                <span class="contents-card-action">View Program</span>
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