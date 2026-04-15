<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/students.css') }}?v={{ filemtime(public_path('assets/css/students.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
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

    <main class="main-content students-review-page">

        <section class="hero-shell">
            <section class="carousel-section">
                <div class="carousel full-carousel">
                    <div class="carousel-stage">
                        <div class="carousel-slide active">
                            <div class="carousel-split" aria-hidden="true">
                                <img src="{{ asset('assets/static_img/about_header_image.png') }}" alt="" class="carousel-half carousel-half-left">
                            </div>
                            <div class="carousel-caption">
                                <h2>STUDENTS</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <section class="academic-shell page-shell">
            <nav class="academic-breadcrumb layout-breadcrumb reveal" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Home</a>
                <span>&gt;</span>
                <strong>Students</strong>
            </nav>
        </section>

        <section class="students-contents-strip reveal">
            <div class="students-contents-inner">
                <div class="students-contents-head">
                    <p class="section-tag">Contents</p>
                </div>

                <nav class="students-cards" aria-label="Student services">

                        {{-- Student Handbook --}}
                        <a href="https://drive.google.com/file/d/0B1BuDAuN0r8SX1BWX2NSN3FURzg/view?resourcekey=0-oi8lUy9PCFysh0FDyL5ipw" target="_blank" rel="noopener noreferrer" class="students-card">
                        <div class="students-card-inner">
                            <div class="students-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Student Handbook">
                                <div class="students-card-copy">
                                    <h3>Student Handbook</h3>
                                </div>
                            </div>
                            <div class="students-card-back">
                                <div class="students-card-overlay-copy">
                                    <h3>Student Handbook</h3>
                                    <p>Guidelines, policies, and procedures that govern student life at PUP Taguig Campus.</p>
                                </div>
                                <span class="students-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                    {{-- PUPSIS --}}
                    <a href="https://sis.pup.edu.ph/" target="_blank" rel="noopener noreferrer" class="students-card">
                        <div class="students-card-inner">
                            <div class="students-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUPSIS">
                                <div class="students-card-copy">
                                    <h3>PUPSIS</h3>
                                </div>
                            </div>
                            <div class="students-card-back">
                                <div class="students-card-overlay-copy">
                                    <h3>PUPSIS</h3>
                                    <p>Access the PUP Student Information System for enrollment, grades, and academic records.</p>
                                </div>
                                <span class="students-card-action">Visit PUPSIS</span>
                            </div>
                        </div>
                    </a>

                    {{-- ODRS --}}
                    <a href="https://odrs.pup.edu.ph/" target="_blank" rel="noopener noreferrer" class="students-card">
                        <div class="students-card-inner">
                            <div class="students-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="ODRS">
                                <div class="students-card-copy">
                                    <h3>ODRS</h3>
                                </div>
                            </div>
                            <div class="students-card-back">
                                <div class="students-card-overlay-copy">
                                    <h3>ODRS</h3>
                                    <p>Request official documents and records online through the PUP Online Document Request System.</p>
                                </div>
                                <span class="students-card-action">Visit ODRS</span>
                            </div>
                        </div>
                    </a>

                    {{-- Downloadable Forms --}}
                    <a href="#" class="students-card">
                        <div class="students-card-inner">
                            <div class="students-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Downloadable Forms">
                                <div class="students-card-copy">
                                    <h3>Downloadable Forms</h3>
                                </div>
                            </div>
                            <div class="students-card-back">
                                <div class="students-card-overlay-copy">
                                    <h3>Downloadable Forms</h3>
                                    <p>Access and download official forms needed for various student transactions and requests.</p>
                                </div>
                                <span class="students-card-action">See more</span>
                            </div>
                        </div>
                    </a>
                </nav>
            </div>
        </section>

{{-- Student Organizations Section --}}
<section class="students-orgs-section reveal">
    <div class="students-orgs-inner">
        <div class="students-orgs-blurred">

        {{-- Academic Orgs --}}
        <div class="students-orgs-group">
            <p class="section-tag">Academic Student Organizations</p>

            <div class="students-orgs-grid">

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="AEES">
                    </div>
                    <div class="students-org-copy">
                        <h3>Association of Electronics Engineering Students</h3>
                        <span class="students-org-abbr">AEES</span>
                    </div>
                </a>

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Computer Society">
                    </div>
                    <div class="students-org-copy">
                        <h3>Computer Society PUP Taguig</h3>
                        <span class="students-org-abbr">CS – PUPT</span>
                    </div>
                </a>

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="JMA">
                    </div>
                    <div class="students-org-copy">
                        <h3>Junior Marketing Association</h3>
                        <span class="students-org-abbr">PUPT JMA</span>
                    </div>
                </a>

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="JPIA">
                    </div>
                    <div class="students-org-copy">
                        <h3>Junior Philippine Institute of Accountants</h3>
                        <span class="students-org-abbr">JPIA – PUP Taguig</span>
                    </div>
                </a>

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PMAP Junior">
                    </div>
                    <div class="students-org-copy">
                        <h3>Junior People Management Association of the Philippines</h3>
                        <span class="students-org-abbr">PMAP Junior – PUPT</span>
                    </div>
                </a>

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Wisdom Values Education">
                    </div>
                    <div class="students-org-copy">
                        <h3>Wisdom Values Education</h3>
                        <span class="students-org-abbr">WVE</span>
                    </div>
                </a>

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Dura Lex Sed Lex">
                    </div>
                    <div class="students-org-copy">
                        <h3>Philippine Association of Students in Office Administration</h3>
                        <span class="students-org-abbr">Dura Lex Sed Lex</span>
                    </div>
                </a>

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PSME">
                    </div>
                    <div class="students-org-copy">
                        <h3>Philippine Society of Mechanical Engineers</h3>
                        <span class="students-org-abbr">PSME – PUPT Student Unit</span>
                    </div>
                </a>

            </div>
        </div>

        {{-- Non-Academic Orgs --}}
        <div class="students-orgs-group">
            <p class="section-tag">Non-Academic Student Organizations</p>

            <div class="students-orgs-grid">

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="ERG">
                    </div>
                    <div class="students-org-copy">
                        <h3>Emergency Response Group</h3>
                        <span class="students-org-abbr">ERG – "Serving with a Purpose"</span>
                    </div>
                </a>

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="iROCK Campus">
                    </div>
                    <div class="students-org-copy">
                        <h3>iROCK Campus</h3>
                        <span class="students-org-abbr">Established 2015</span>
                    </div>
                </a>

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP UKAW">
                    </div>
                    <div class="students-org-copy">
                        <h3>PUP UKAW</h3>
                        <span class="students-org-abbr">UKAW</span>
                    </div>
                </a>

                <a href="#" target="_blank" rel="noopener noreferrer" class="students-org-card">
                    <div class="students-org-img-wrap">
                        <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP-REC">
                    </div>
                    <div class="students-org-copy">
                        <h3>PUP-REC</h3>
                        <span class="students-org-abbr">REC</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>
    </main>

    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>