<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academics - Polytechnic University of the Philippines</title>
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

        <section class="hero-shell">
            <section class="carousel-section">
                <div class="carousel full-carousel">
                    <div class="carousel-stage">
                        <div class="carousel-slide active">
                            <div class="carousel-split" aria-hidden="true">
                                <img src="{{ asset('assets/static_img/about_header_image.png') }}" alt="" class="carousel-half carousel-half-left">
                            </div>
                            <div class="carousel-caption">
                                <h2>ACADEMICS</h2>
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
                <strong>Academics</strong>
            </nav>
        </section>

        <section class="contents-strip reveal">
            <div class="contents-strip-inner">
                <div class="contents-strip-head">
                    <p class="section-tag">Contents</p>
                </div>

                <nav class="contents-cards show-card-action" aria-label="Academic page contents">
                    <a href="{{ route('public.degree-programs') }}" class="contents-card card_without_section">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Degree Programs">
                                <div class="contents-card-copy">
                                    <h3>Degree Programs</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>Degree Programs</h3>
                                    <p>Discover a wide range of undergraduate majors and minors designed to prepare you for professional success.</p>
                                </div>
                                <span class="contents-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('public.diploma-programs') }}" class="contents-card card_without_section">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Diploma Programs">
                                <div class="contents-card-copy">
                                    <h3>Diploma Programs</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>Diploma Programs</h3>
                                    <p>Gain practical skills and specialized knowledge through diploma courses tailored for career readiness.</p>
                                </div>
                                <span class="contents-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('public.graduate-programs') }}" class="contents-card card_without_section">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Graduate Programs">
                                <div class="contents-card-copy">
                                    <h3>Graduate Programs</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>Graduate Programs</h3>
                                    <p>Advance your expertise with master's and doctoral programs that foster research, leadership, and innovation.</p>
                                </div>
                                <span class="contents-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('public.pup-iapply') }}" class="contents-card card_without_section">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP iApply">
                                <div class="contents-card-copy">
                                    <h3>PUP iApply</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>PUP iApply</h3>
                                    <p>Easily access the university's online application portal to start your academic journey.</p>
                                </div>
                                <span class="contents-card-action">See more</span>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('public.university-calendar') }}" class="contents-card card_without_section">
                        <div class="contents-card-inner">
                            <div class="contents-card-front">
                                <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="University Calendar">
                                <div class="contents-card-copy">
                                    <h3>University Calendar</h3>
                                </div>
                            </div>
                            <div class="contents-card-back">
                                <div class="contents-card-overlay-copy">
                                    <h3>University Calendar</h3>
                                    <p>Stay updated with important academic schedules, events, and deadlines throughout the school year.</p>
                                </div>
                                <span class="contents-card-action">See more</span>
                            </div>
                        </div>
                    </a>
                </nav>
            </div>
        </section>

        <section class="academic-intro-text reveal">
            <div class="academic-intro-text-inner">
                <p><strong>Quality and relevant education</strong> that responds to the call of present times in building the <strong>foundations of the future.</strong></p>
                <p>Ranging from high school to doctoral courses, traditional to nontraditional education system, <strong>the University makes it possible</strong> that <strong>deserving individuals can have access</strong> to these academic resources.</p>
                <p>The University has always been making <strong>initiatives to enrich its academic programs</strong> in various fields of study and <strong>implement an educational strategy</strong> designed to provide our students with highly employable, managerial, and entrepreneurial skills in order to make them exceedingly <strong>creative, productive, competitive, and self-reliant</strong>.</p>
            </div>
        </section>

        <section class="academic-features reveal">
            <div class="academic-features-inner layout-inset">
                <p class="academic-features-eyebrow layout-kicker">What we offer</p>
                <div class="academic-features-grid">
                    <div class="academic-feature-card cards_information">
                        <div class="academic-feature-card-accent"></div>
                        <h3 class="academic-feature-title">QUALITY</h3>
                        <p><strong>Academic Excellence</strong> Being one of the reputable universities in the country, we always make it to a point that the education given to our students meets the standards of quality and excellence.</p>
                    </div>
                    <div class="academic-feature-card cards_information">
                        <div class="academic-feature-card-accent"></div>
                        <h3 class="academic-feature-title">RELEVANT</h3>
                        <p><strong>Responsive Learning</strong> The University, through its various programs, equips its students with learning and skills that are significant and responsive, enabling students to be competitive and very resourceful.</p>
                    </div>
                    <div class="academic-feature-card cards_information">
                        <div class="academic-feature-card-accent"></div>
                        <h3 class="academic-feature-title">FLEXIBLE</h3>
                        <p><strong>Accessible Study Paths</strong> Programs that adapt to a student's living condition, especially for the working class. Our Open University and distance learning method goes beyond the physical restrictions of a campus.</p>
                    </div>
                    <div class="academic-feature-card cards_information">
                        <div class="academic-feature-card-accent"></div>
                        <h3 class="academic-feature-title">ACCREDITED</h3>
                        <p><strong>Recognized Standards</strong> Most of our academic courses are accredited by the Accrediting Agency of Chartered Colleges and Universities in the Philippines (AACCUP).</p>
                    </div>
                    <div class="academic-feature-card academic-feature-card--wide cards_information">
                        <div class="academic-feature-card-accent"></div>
                        <h3 class="academic-feature-title">AFFORDABLE</h3>
                        <p><strong>Low-Cost Education</strong> Practicality without sacrificing quality in education. Having the lowest tuition and fees among universities in the Philippines, one can enroll for less than PHP 500 per semester in an undergraduate program.</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>